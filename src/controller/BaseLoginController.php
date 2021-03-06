<?php
/**
 *  +----------------------------------------------------------------------
 *  | Created by  hahadu (a low phper and coolephp)
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2020. [hahadu] All rights reserved.
 *  +----------------------------------------------------------------------
 *  | SiteUrl: https://github.com/hahadu
 *  +----------------------------------------------------------------------
 *  | Author: hahadu <582167246@qq.com>
 *  +----------------------------------------------------------------------
 *  | Date: 2020/10/5 下午3:52
 *  +----------------------------------------------------------------------
 *  | Description:   ImAdminThink
 *  +----------------------------------------------------------------------
 **/

declare (strict_types = 1);

namespace Hahadu\ThinkUserLogin\controller;
use Hahadu\Helper\StringHelper;
use Hahadu\ThinkUserLogin\Builder\JWTBuilder;
use Hahadu\ThinkUserLogin\Traits\BaseUsersTrait;
use Hahadu\ThinkUserLogin\Traits\EmailRegisterTrait;
use Hahadu\ThinkUserLogin\Traits\SmsRegisterTrait;
use Hahadu\ThinkUserLogin\validate\BaseUserLogin;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Session;
use think\Request;

class BaseLoginController
{
    use BaseUsersTrait;
    use EmailRegisterTrait;
    use SmsRegisterTrait;
    protected $middleware = [\think\middleware\SessionInit::class];
    protected $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0123456789';
    protected $mail_verify;
    protected $sms_verify;
    /****
     * @var \Hahadu\ThinkBaseModel\BaseModel
     */
    protected $users;
    protected $jwt_login;
    private $user_database;

    public function __construct(){
        $this->mail_verify = StringHelper::create_rand_string(6,$this->chars);
        $this->sms_verify = StringHelper::create_rand_string(4,'0123456789');
        $this->user_database = Config::get('login.user_model');
        $this->users = $this->user_data();
        $this->jwt_login = Config::get('login.JWT_login');
    }
    public function login(){
        if(request()->isPost()){
                $post_data = request()->post();
                try{
                    validate(BaseUserLogin::class)->check($post_data);
                }catch (ValidateException $e){
                    return wrap_msg_array($e->getMessage());
                }
                $map = [
                    'username' => $post_data['username'],
                ];

                $data = $this->users::where($map)->findOrEmpty();
                if($data->isEmpty()){
                    return wrap_msg_array('420113','用户名不存在');
                }

                if(is_object($data)){
                    $data = $data->toArray();
                }
                if(StringHelper::password($post_data['password'],$data['password'])){
                    $session = array(
                        'id' =>$data['id'],
                        'username'=>$data['username'],
                        'avatar'=>$data['avatar'],
                    );
                    $result = Session::set('user',$session);

                    if($this->jwt_login==true){
                        $payloads=[
                            'uid'=>$data['id'],
                        ];
                        $jwt = new JWTBuilder($data['id'],$data['password'],$payloads);
                        $token = (string) $jwt->token;
                        $result = [
                            'code'=>100003,
                            'token'=> $token,
                        ];
                        return wrap_msg_array(100003,'成功',[],$result);
                    }
                    if(Session::has('user') or $result){
                        return wrap_msg_array(100003,'登录成功');
                    }
                }else{
                    return wrap_msg_array(420103,'账号密码错误');
                }
        }else{
            return wrap_msg_array(0,'数据格式错误');
        }
    }

    /****
     * 退出登录
     * @return array
     */
    public function logout(){
        Session::delete('user');
        if(!Session::has('user')){
            return wrap_msg_array(100004,'注销成功');
        }else{
            Session::set('user',null);
            if(null !== Session::get('user'))
            return wrap_msg_array(402104,'注销登录失败');
        }
    }


    /******
     * 修改密码
     * @param Request $request
     */
    public function re_password(Request $request){
        $data = $request->post();
        try {
            throw_unless($data['sms_verify'],'think\exception\ValidateException','420116');
            validate(BaseUserLogin::class)->check($data);
        }catch (ValidateException $e){
            return wrap_msg_array($e->getMessage());
        }
        $map['username'] = $data['username'];
        $map['phone'] = $data['phone'];
        $userdata = $this->users->where($map);
        $check = $userdata->findOrEmpty()->isEmpty();
        if(!$check){
            $re_password = $userdata->data(['password'=>StringHelper::password($data['password'])])->update();
            if($re_password){
                $result = wrap_msg_array(100011,'密码修改成功');
            }
        }else{
            $result = wrap_msg_array(0,'手机号或用户名不匹配');
        }
        return $result;
    }

    protected function email_tpl(){
        return ['title'=>'欢迎注册，请查收验证码','content'=>'您好，感谢您的注册您的验证码是: %s'];
    }

}
