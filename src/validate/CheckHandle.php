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
 *  | Date: 2020/11/12 下午4:07
 *  +----------------------------------------------------------------------
 *  | Description:   UserLogin
 *  +----------------------------------------------------------------------
 **/

namespace Hahadu\ThinkUserLogin\validate;


use Hahadu\ThinkJumpPage\JumpPage;
use Hahadu\ThinkUserLogin\Builder\JWTBuilder;
use think\facade\Config;

class CheckHandle
{
    private static $user;

    public function __construct()
    {
        self::$user = Config::get('login.user_model');
    }

    static public function jwt_check($token)
    {
        new self();

        $uid = self::get_uid($token);

        $password = self::get_user_password($uid);

        $result = self::check_hash($uid, $password);

        if (true === $result) {

            return $result;

        } else {

            JumpPage::jumpPage($result)->send();

        }

    }

    private static function get_uid($token)
    {
        $parser = JWTBuilder::parser($token);

        if (is_int($parser)) {

            JumpPage::jumpPage($parser)->send();

        }

        return $parser->getClaim('uid');

    }

    private static function get_user_password($uid)
    {

        $users = new self::$user();

        return $users::getFieldBy($uid, 'password');

    }

    private static function check_hash($uid, $hash_key)
    {

        $check = new JWTBuilder($uid, $hash_key);

        return $check->jwt_check($hash_key);

    }


}