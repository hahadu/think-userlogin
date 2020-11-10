<?php
/**
 *  +----------------------------------------------------------------------
 *  | Created by  hahadu (a low phper and coolephp)
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2020. [hahadu] All rights reserved.
 *  +----------------------------------------------------------------------
 *  | SiteUrl: https://github.com/hahadu/wechat
 *  +----------------------------------------------------------------------
 *  | Author: hahadu <582167246@qq.com>
 *  +----------------------------------------------------------------------
 *  | Date: 2020/11/3 下午11:52
 *  +----------------------------------------------------------------------
 *  | Description:   BaseUser
 *  +----------------------------------------------------------------------
 **/

namespace Hahadu\ThinkUserLogin\Traits;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use think\captcha\facade\Captcha;

trait BaseUsersTrait
{
    protected  $jwt;

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
     * @return mixed
     */
    protected function user_data(){
        return new $this->user_database();
    }



}