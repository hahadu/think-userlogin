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


trait BaseUsersTrait
{
    /**
     * 发送邮件
     * @param  string $address 接收邮件的邮箱地址 发送给多个地址需要写成数组形式
     * @param  string $subject 标题
     * @param  string $content 内容
     * @return boolean|mixed   是否成功
     */
    function send_email($address,$subject,$content,$smtp)
    {
        $email_smtp = $smtp['smtp_address'];
        $email_username = $smtp['smtp_username'];
        $email_password = $smtp['smtp_password'];
        $email_from_name = $smtp['smtp_from_name'];
        $email_smtp_secure = $smtp['smtp_secure'];
        $email_port = $smtp['smtp_port'];
        if (empty($email_smtp) || empty($email_username) || empty($email_password) || empty($email_from_name)) {
            return json_encode(array("error" => 1, "message" => '邮箱配置不完整'));
        }
        try {
            $phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
            // 设置PHPMailer使用SMTP服务器发送Email
            $phpmailer->IsSMTP();
            // 设置设置smtp_secure
            $phpmailer->SMTPSecure = $email_smtp_secure;
            // 设置port
            $phpmailer->Port = $email_port;
            // 设置为html格式<strong></strong>
            $phpmailer->IsHTML(true);
            // 设置邮件的字符编码'
            $phpmailer->CharSet = 'UTF-8';
            // 设置SMTP服务器。
            $phpmailer->Host = $email_smtp;
            // 设置为"需要验证"
            $phpmailer->SMTPAuth = true;
            // 设置用户名
            $phpmailer->Username = $email_username;
            // 设置密码
            $phpmailer->Password = $email_password;
            // 设置邮件头的From字段。
            $phpmailer->From = $email_username;
            // 设置发件人名字
            $phpmailer->FromName = $email_from_name;
            // 添加收件人地址，可以多次使用来添加多个收件人
            if (is_array($address)) {
                foreach ($address as $addressv) {
                    $phpmailer->AddAddress($addressv);
                }
            } else {
                $phpmailer->AddAddress($address);
            }
            // 设置邮件标题
            $phpmailer->Subject = $subject;
            // 设置邮件正文
            $phpmailer->Body = $content;
            // 发送邮件。
            return $phpmailer->Send();

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return json_encode($e);
        }
    }


    }