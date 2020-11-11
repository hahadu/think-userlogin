<?php
namespace Hahadu\ThinkUserLogin\validate;
use think\facade\Session;
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
    //    'captcha|验证码'=>'require|captcha',
        'username' => 'require',
        'email' => 'email',
        'password' => 'require|alphaNum',
        'repassword' =>  'confirm:password',
        'email_verify' =>'checkEmailCode'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
    //    'captcha.require' => '420109', //验证码必填
    //    'captcha.captcha' => '420105', //验证码错误
        'username.require' => '420108', //用户名必填
        'password.require' => '420110', //密码必填
        'repassword.confirm' => '420111', //重复密码错误
        'email_verify.checkEmailCode' => '420107', //邮箱验证失败
    ];
    // 自定义验证规则
    protected function checkEmailCode($value)
    {
        return password_verify($value,Session::get('email_verify.key')) ? true : 420107;
    }

}