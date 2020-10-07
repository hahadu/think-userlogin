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
use Hahadu\ThinkUserLogin\validate\BaseUserLogin;
use think\exception\ValidateException;
use think\facade\Session;
use think\captcha\facade\Captcha;
use think\facade\Db;
class BaseLoginController
{
    protected $user_data;
    protected $middleware = [\think\middleware\SessionInit::class];
    public function __construct(){
        $this->user_data = Db::name('users');
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
                $data = $this->user_data()->where($map)->find();
                if(is_object($data)){
                    $data = $data->toArray();
                }
                if(md5($post_data['password'])==$data['password']){
                    $session = array(
                        'id' =>$data['id'],
                        'username'=>$data['username'],
                        'avatar'=>$data['avatar'],
                    );
                    $result = Session::set('user',$session);
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

    /****
     * 验证码
     * @return \think\Response
     */
    public function verify()
    {
        return Captcha::create();
    }

    /****
     * 实例化用户表
     * @return Db
     */
    protected function user_data(){
        return $this->user_data;
    }
}
