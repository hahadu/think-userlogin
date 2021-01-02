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
use Hahadu\Sms\think\ThinkSmsClient;
use think\captcha\facade\Captcha;
use think\facade\Session;

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
        $send_sms = ThinkSmsClient::send_sms($phone,$send_data);
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