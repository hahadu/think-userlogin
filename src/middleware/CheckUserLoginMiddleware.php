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
use Hahadu\ImAdminThink\model\Users;
use Lcobucci\JWT\Builder;
use think\facade\Config;
use think\facade\Session;
use Hahadu\ThinkJumpPage\JumpPage;
use Hahadu\ThinkUserLogin\Builder\JWTBuilder;



class CheckUserLoginMiddleware{
    /****
     * @param Request $request
     * @param \Closure $next
     * @return array|mixed|string
     */
    public function handle($request, \Closure $next){
        $user = Config::get('login.user_model');
        //验证是否登录
        if(Config::get('login.JWT_login')==true){
            $token = $request->param(Config::get('login.token_name'));
            $parser = JWTBuilder::parser($token);
            $uid = $parser->getClaim('uid');
            $users = new $user();
            $username =$users::getFieldBy(1,'username');
            $check = new JWTBuilder($username,$uid);
            if(!$check->jwt_check($token)){
                if(config('jumpPage.ajax')){
                    return JumpPage::jumpPage(420102);
                }else{
                    JumpPage::jumpPage(420102,'/admin/login')->send();
                }
            }
        }elseif (Session::get('user.id')==null){
            if(config('jumpPage.ajax')){
                return JumpPage::jumpPage(420102);
            }else{
                JumpPage::jumpPage(420102,'/admin/login')->send();
            }
        }
        return $next($request);
    }
}