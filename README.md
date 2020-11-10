# think-userlogin
thinkphp用户登录模块
* 用户登录
* 用户注册
>* 验证邮箱注册
  
安装：composer require hahadu/think-userlogin

依赖项 ： 
>* thinkphp验证码模块 topthink/think-captcha
>* 发送验证邮件 phpmailer/phpmailer

做了用户名、密码、验证码的基本验证和登录成功后的session创建

使用：在应用创建一个登录控制器

```php
//用户登录模块控制器Login.php
namespace app\user\controller;
use Hahadu\ThinkUserLogin\controller\BaseLoginController;
use think\facade\View;

class Login extends BaseLoginController
{
    public function index() //用户登录页面
    {
        return view();
    }
    public function login()
    {
        $result = parent::login();
        if($result == 100003){
            return 登录成功;
        }else{
            return 登录失败;
        }
    }
    public function logout()
    {
        $result = parent::logout();
        if($result == 100004){
            return '退出登录成功';
        }else{
            return '退出登录失败';
        }
    }
}

```
### 配置
引入用户表
* 在config/login.php配置登录信息
```php
return [
    'user_model'=> "\Hahadu\ImAdminThink\model\Users", //用户数据表模型路径
    'JWT_login' =>true, //是否开启JWT鉴权 true 开启 false关闭
    'token_name' => 'token' //token表单字段名
];
```

如需指定其他用户表只需在当前控制器中创建一个user_data()即可：
```php

protected function user_data()
    {
        return Db::name('users'); //把users替换为你的表名
    }
用户表必须字段：
用户名：username
密码 ： password
头像 ： avatar 
头像为图片链接地址 
```
如需自己定义验证码，只需按照tp6的验证码文档操作即可
```php
    public function verify()
    { 
        /*
         * 此处写你的验证码方法
         */
        return Captcha::create();
    }
```
关于返回状态码：
* 100003 登录成功
* 100004 退出登录成功
* 420103 登录失败
* 420104 退出登录失败
* 420107 邮箱验证失败
* 420109 验证码必填
* 420108 用户名必填
* 420110 密码必填
* 420111 重复密码错误
