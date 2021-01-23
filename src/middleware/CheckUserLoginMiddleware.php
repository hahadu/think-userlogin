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
 *  | Date: 2020/9/29 下午12:25
 *  +----------------------------------------------------------------------
 *  | Description:   cooleAdmin 检测用户是否登录
 *  +----------------------------------------------------------------------
 **/

namespace Hahadu\ThinkUserLogin\middleware;
use app\Request;
use Hahadu\ThinkUserLogin\validate\CheckHandle;
use think\facade\Config;
use think\facade\Session;
use Hahadu\ThinkJumpPage\JumpPage;



class CheckUserLoginMiddleware{
    /****
     * @param Request $request
     * @param \Closure $next
     * @return array|mixed|string
     */
    public function handle($request, \Closure $next){
        $login_url = null!==Config::get('login.login_url')?Config::get('login.login_url'):'login/index';
        //验证是否登录
        if(Config::get('login.JWT_login')==true){
            $token = $request->param(Config::get('login.token_name'));
            $check = CheckHandle::jwt_check($token);
            if(!$check){
                JumpPage::jumpPage(420102,$login_url)->send();
            }
        }elseif (Session::get('user.id')==null){
            $redirect = [
                'url' => trim($request->baseUrl(),'.'.config('route.url_html_suffix')),
                'query' => $request->get(),
            ];
            Session::delete('login');
            Session::set('login.redirect',$redirect);

            JumpPage::jumpPage(420102,$login_url)->send();
        }
        return $next($request);
    }
}