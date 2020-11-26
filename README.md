# think-userlogin
thinkphp用户登录模块
>* 用户登录
>* 用户注册
>* 验证邮箱注册
>* 验证用户登录
>* 登录鉴权方式：session/cookie 或JWT 方式鉴权

  
安装：composer require hahadu/think-userlogin

依赖项 ： 
>* thinkphp验证码模块 topthink/think-captcha
>* 发送验证邮件 phpmailer/phpmailer
>* 返码状态 hahadu/think-jump-class 


做了用户名、密码、验证码的基本验证和登录成功后的session创建

使用：
* 在应用创建一个登录控制器
* 该控制器继承Hahadu\ThinkUserLogin\controller\BaseLoginController控制器

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
        if($result['code'] == 100003){
            return '登录成功';
        }else{
            return '登录失败';
        }
    }
    public function logout()
    {
        $result = parent::logout();
        if($result['code'] == 100004){
            return '退出登录成功';
        }else{
            return '退出登录失败';
        }
    }
}

```
### 配置
##### 用户表配置
* 在config/login.php配置登录信息
```php
return [
    'user_model'=> "\app\model\Users", //用户数据表模型路径
    'login_url' =>'/admin/login', //用户需要登录时的跳转登录页面 /模块/控制器/方法
    'JWT_login' =>true, //是否开启JWT鉴权 true 开启 false关闭
    'token_name' => 'token',//token表单字段名
    'JWT'=>[ //配置jwt 开启jwt有效
        'nbf'=> 1, //令牌生效开始时间 *距离令牌创建的时间
        'exp'=> 3600, //令牌过期时间 *距离令牌创建的时间
        'iss'=> request()->host(), //iss
        'aud'=> request()->host(), //aud

    ]

];
```
```php
//用户数据表说明
/*
用户表必须字段：
用户名：username
密码 ： password
头像 ： avatar 
头像为图片链接地址 
*/
```
##### 页面跳转配置
```php
//页面跳转遵循hahadu/think-jump-class 的跳转方式
//
return [
    //是否开启ajax返回 true 开启/ false关闭
    'ajax'=>true,
    'ajax_type'=> 'jsonp', //支持json|jsonp|xml3种类型、开启ajax有效
    //自定义跳转模板文件路径
    'dispatch_tpl' => '' ,
];
```
##### 邮箱验证注册

```php

    public function register(){
        $result = parent::email_register();
        if($result['code']==100002){
         return '注册成功';
        }else
            return  '注册失败';
        }
    }
//在email_tpl方法中设置邮箱模板内容，
//邮箱模板是一个数组，必须包含'title'和'content'
//或者复制下面的方法到您的控制器中，按需修改即可
    protected function email_tpl(){
        return ['title'=>'欢迎注册，请查收验证码','content'=>'您好，感谢您的注册，您的验证码是: %s'];
    }


```
获取邮箱验证码也是非常简单
```html
//获取邮箱验证码直接在模板文件中get当前控制器中的get_email_code方法即可
 <a href={:url('get_email_code')}> 获取邮箱验证码</a>;

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
