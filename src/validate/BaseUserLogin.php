<?php
namespace Hahadu\ThinkUserLogin\validate;
use think\Validate;
class BaseUserLogin extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'captcha|验证码'=>'require|captcha',
        'username' => 'require',
        'password' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'captcha.require' => '420105', //验证码必填
        'username.require' => '420106', //用户名必填
        'password.require' => '420107', //密码必填
    ];

}