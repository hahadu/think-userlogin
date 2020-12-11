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
use Hahadu\Helper\JsonHelper;
use Hahadu\Helper\StringHelper;
use Hahadu\ThinkUserLogin\Builder\JWTBuilder;
use Hahadu\ThinkUserLogin\Traits\BaseUsersTrait;
use Hahadu\ThinkUserLogin\validate\BaseUserLogin;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Session;
use Hahadu\Sms\think\ThinkSmsClient;
class BaseLoginController
{
    use BaseUsersTrait;
    protected $middleware = [\think\middleware\SessionInit::class];
    protected $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0123456789';
    protected $mail_verify;
    protected $sms_verify;
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
                $data = $this->users::where($map)->find();
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

    /****
     * 邮箱验证注册
     * @return array|int|string
     */
    public function email_register(){
        $data = request()->post();
        try{
            validate(BaseUserLogin::class)->check($data);
        }catch (ValidateException $e){
            return $e->getError();
        }
        Session::delete('email_verify');
        $map = [
            'username' => $data['username'],
        ];
        $check = $this->users::where($map)->findOrEmpty();
        if(!$check->isEmpty()){
            $result = wrap_msg_array(420112,'用户名已存在'); //用户名已存在
        }else{
            $data['password'] = StringHelper::password($data['password']);
            $data['last_login_ip'] = request()->ip();
            $data['register_time'] = time();
            $result = $this->users->addData($data);
            if($result){
                $register_id = ['uid'=>$result];
                $result = wrap_msg_array(100002,'注册成功',$register_id); //注册成功
            }
        }
        return $result;
    }
    /****
     * 短信验证注册
     * @return array|int|string
     */
    public function sms_register(){
        $data = request()->post();
        try{
            validate(BaseUserLogin::class)->check($data);
        }catch (ValidateException $e){
            return $e->getError();
        }
        //Session::delete('sms_verify');
        $map = [
            'username' => $data['username'],
        ];
        $check = $this->users::where($map)->findOrEmpty();
        if(!$check->isEmpty()){
            $result = wrap_msg_array(420112,'用户名已存在'); //用户名已存在
        }else{
            $data['password'] = StringHelper::password($data['password']);
            $data['last_login_ip'] = request()->ip();
            $data['register_time'] = time();
            $result = $this->users->addData($data);
            if($result){
                $register_id = ['uid'=>$result];
                $result = wrap_msg_array(100002,'注册成功',$register_id); //注册成功
            }
        }
        return $result;
    }

    /****
     * 发送邮箱验证码到用户邮箱
     * @return mixed
     */
    public function get_email_code(){
        $email = request()->post('email');
        $mail_tpl = $this->email_tpl();
        $smtp = config('smtp');
        $content = sprintf($mail_tpl['content'],$this->mail_verify);
        if(send_email($email,$mail_tpl['title'],$content,$smtp)){
            $hash = password_hash($this->mail_verify,PASSWORD_BCRYPT,['cost' => 12]);
            Session::set('email_verify.key',$hash);
            Session::set('email_verify.time',time());
            return json(wrap_msg_array(1,'成功'));
        }else{
            return json(wrap_msg_array(0,'失败'));
        }
    }
    /****
     * 发送短信验证码到用户手机
     * @return array|false|string|null
     */
    public function get_sms_code(){
        $phone = request()->post('phone');
        $sms_verify = $this->sms_verify;
        $send_data  = ["code"=>$sms_verify];
        $send_sms = ThinkSmsClient::send_sms($phone,$send_data);
        if(strtoupper($send_sms['Code'])==='OK'){
            $hash = password_hash($sms_verify,PASSWORD_BCRYPT,['cost' => 12]);
            Session::set('sms_verify.key',$hash);
            Session::set('sms_verify.time',time());
            return json(wrap_msg_array(1,'成功'));
        }else{
            return json(wrap_msg_array(0,$send_sms['Message']));
        }
    }

    protected function email_tpl(){
        return ['title'=>'欢迎注册，请查收验证码','content'=>'您好，感谢您的注册您的验证码是: %s'];
    }

}
