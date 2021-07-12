# think-userlogin
thinkphp用户登录模块
>* 用户登录
>* 用户注册
>* 验证邮箱注册
>* 验证用户登录
>* 手机短信注册
>* 登录鉴权方式：session/cookie 或JWT 方式鉴权
>* 支持登陆后跳转到登陆前请求页面

  
安装：composer require hahadu/think-userlogin

默认安装依赖项 ： 
>* thinkphp验证码模块 topthink/think-captcha
>* 发送验证邮件 phpmailer/phpmailer
>* 发送验证短信 hahadu/sms
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
用户表字段：
用户名：username //必须
密码 ： password //必须
头像 ： avatar  //必须
用户邮箱： email //可选 需要邮箱注册登录的场景可用
用户手机： phone //可选 需要短信注册登录的场景可用
头像为图片链接地址 
*/
```
##### 页面跳转配置
```php
//页面跳转遵循hahadu/think-jump-class 的跳转方式
//
return [
    //是否开启ajax返回 true 开启/ false关闭
    //开启JWT鉴权时，ajax必须设置为true
    'ajax'=>true,
    'ajax_type'=> 'json', //支持json|jsonp|xml3种类型、开启ajax有效
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
##### 短信验证注册
短信使用 [hahadu/sms](“https://github.com/hahadu/sms”) 模块
近期短信模块重大升级，升级本模块后请务必修改短信模块的配置项！详情参考： https://github.com/hahadu/sms
```php

    public function register(){
        $result = parent::sms_register();
        if($result['code']==100002){
         return '注册成功';
        }else
            return  '注册失败';
        }
    }




```
##### 修改密码
```php
  public function repassword(Request $request){
     return $this->re_password($request);
  }
```

获取邮箱或者短信验证码也是非常简单
```javascript
//获取注册验证码直接在模板文件中post请求到当前控制器中的get_email_code方法即可
 $.post("{:url('get_email_code')}", {email: $email}, // 获取邮箱验证码;
 $.post("{:url('get_sms_code')}", {phone: $phone}, // 获取短信验证码;

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

  交流qq群 [(点击链接加入群聊【thinkphp6开发交流群】：839695142]https://jq.qq.com/?_wv=1027&k=FxgUKLhJ)
