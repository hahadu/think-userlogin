<?php


namespace Hahadu\ThinkUserLogin\Traits;


use Hahadu\Helper\StringHelper;
use Hahadu\Sms\think\ThinkSmsClient;
use Hahadu\ThinkUserLogin\validate\BaseUserLogin;
use think\exception\ValidateException;
use think\facade\Session;

trait SmsRegisterTrait
{
    /****
     * 短信验证注册
     * @return array|int|string
     */
    public function sms_register(){
        $data = request()->post();
        try{
            throw_unless($data['sms_verify'],'think\exception\ValidateException','420116');
            validate(BaseUserLogin::class)->check($data);
        }catch (ValidateException $e){
            return wrap_msg_array($e->getError(),'注册失败');
        }

        //Session::delete('sms_verify');
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
     * 发送短信验证码到用户手机
     * @return array|false|string|null
     */
    public function get_sms_code(){
        $phone = request()->post('phone');
        if(!check_phone($phone)){
            return  json(wrap_msg_array(0,'手机号码不合法'));
        };
        $sms_verify = $this->sms_verify;
        $send_data  = ["code"=>$sms_verify];
        $send_sms = ThinkSmsClient::init()->send_sms($phone,$send_data);
        if(strtoupper($send_sms['Code'])==='OK'){
            $hash = password_hash($sms_verify,PASSWORD_BCRYPT,['cost' => 12]);
            Session::set('sms_verify.key',$hash);
            Session::set('sms_verify.time',time());
            Session::set('sms_verify.phone',$phone);
            return json(wrap_msg_array(1,'成功'));
        }else{
            return json(wrap_msg_array(0,$send_sms['Message']));
        }
    }

}