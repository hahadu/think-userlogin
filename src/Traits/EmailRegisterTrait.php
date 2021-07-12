<?php


namespace Hahadu\ThinkUserLogin\Traits;


use Hahadu\Helper\StringHelper;
use Hahadu\Sms\think\ThinkSmsClient;
use Hahadu\ThinkUserLogin\validate\BaseUserLogin;
use think\exception\ValidateException;
use think\facade\Session;

trait EmailRegisterTrait
{
    /****
     * 邮箱验证注册
     * @return array|int|string
     */
    public function email_register(){
        $data = request()->post();
        try{
            throw_unless($data['email_verify'],'Exception','邮箱验证码不能为空');
            validate(BaseUserLogin::class)->check($data);
        }catch (ValidateException $e){
            return wrap_msg_array($e->getError(),'注册失败');
        }
        Session::delete('email_verify');
        $map = [
            'username' => $data['username'],
        ];
        $check = $this->users::where($map)->findOrEmpty();
        if(!$check->isEmpty()){
            $result = wrap_msg_array(420112,'用户名已存在'); //用户名已存在
        }else{
            $data['password'] = StringHelper::password($data['password']);
            $data['last_login_ip'] = request()->ip();
            $data['register_time'] = time();
            $result = $this->users->addData($data);
            if($result){
                $register_id = ['uid'=>$result];
                $result = wrap_msg_array(100002,'注册成功',$register_id); //注册成功
            }
        }
        return $result;
    }

    /****
     * 发送邮箱验证码到用户邮箱
     * @return mixed
     */
    public function get_email_code(){
        $email = request()->post('email');
        $mail_tpl = $this->email_tpl();
        $smtp = config('smtp');
        $content = sprintf($mail_tpl['content'],$this->mail_verify);
        if(send_email($email,$mail_tpl['title'],$content,$smtp)){
            $hash = password_hash($this->mail_verify,PASSWORD_BCRYPT,['cost' => 12]);
            Session::set('email_verify.key',$hash);
            Session::set('email_verify.time',time());
            return json(wrap_msg_array(1,'成功'));
        }else{
            return json(wrap_msg_array(0,'失败'));
        }
    }



}