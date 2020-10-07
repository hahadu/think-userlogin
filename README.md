# think-userlogin
thinkphp用户登录模块

安装：composer require hahadu/think-userlogin

依赖项 ： thinkphp验证码模块 topthink/think-captcha

做了用户名、密码、验证码的基本验证和登录成功后的session创建

使用：在应用创建一个登录控制器

```
用户登录模块控制器Login.php
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

默认用户表为users

如需指定其他用户表只需在当前控制器中创建一个user_data()即可：
```puml

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
```puml
    public function verify()
    { 
        /*
         * 此处写你的验证码方法
         */
        return Captcha::create();
    }
```
