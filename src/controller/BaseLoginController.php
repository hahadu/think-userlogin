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
use Hahadu\ThinkUserLogin\validate\BaseUserLogin;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Session;
class BaseLoginController
{
    use BaseUsersTrait;
   // protected $user_login_data;
    protected $middleware = [\think\middleware\SessionInit::class];
    protected $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0123456789';
    protected $mail_verify;
    protected $users;
    protected $jwt_login;
    private $user_database;

    public function __construct(){
        $this->mail_verify = StringHelper::create_rand_string(6,$this->chars);
        $this->user_database = Config::get('login.user_model');
        $this->users = $this->user_data();
        $this->jwt = Config::get('login.JWT');
       // $this->user_login_data = Db::name('users');
    }
    public function login(){
        if(request()->isPost()){
                $post_data = request()->post();
                try{
                    validate(BaseUserLogin::class)->check($post_data);
                }catch (ValidateException $e){
                    return $e->getError();
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
                        $jwt = new JWTBuilder($data['username'],$data['id'],$payloads);
                        $token = $jwt->token;
                        $result = [
                            'code'=>100003,
                            'token'=> $token,
                        ];
                        file_put_contents('token.txt',$token);
                        return $result;
                    }
                    if(Session::has('user') or $result){
                        return 100003;
                    }
                }else{
                    return 420103;
                }
        }
    }

    /****
     * 退出登录
     *
     *
     */
    public function logout(){
        Session::delete('user');
        if(!Session::has('user')){
            return 100004;
        }else{
            Session::set('user',null);
            if(null !== Session::get('user'))
            return 402104;
        }
    }
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
            $result = 420112; //用户名已存在
        }else{
            $data['password'] = StringHelper::password($data['password']);
            $data['last_login_ip'] = request()->ip();
            $data['register_time'] = time();
            $result = $this->users->addData($data);
            if($result){
                $result = 100002; //注册成功
            }
        }
        return $result;
    }

    /****
     * 发送邮箱验证码到用户邮箱
     * @return false|int|string|null
     */
    public function get_email_code(){
        $email = request()->post('email');
        $smtp = config('smtp');
        if(send_email($email,'注册验证码',$this->mail_verify,$smtp)){
            $hash = password_hash($this->mail_verify,PASSWORD_BCRYPT,['cost' => 12]);
            Session::set('email_verify.key',$hash);
            Session::set('email_verify.time',time());
            return $hash;
        }else{
            return 0;
        }
    }

}
