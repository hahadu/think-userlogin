<?php
/**
 *  +----------------------------------------------------------------------
 *  | Created by  hahadu (a low phper and coolephp)
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2020. [hahadu] All rights reserved.
 *  +----------------------------------------------------------------------
 *  | SiteUrl: https://github.com/hahadu/think-user login
 *  +----------------------------------------------------------------------
 *  | Author: hahadu <582167246@qq.com>
 *  +----------------------------------------------------------------------
 *  | Date: 2020/11/10 下午7:04
 *  +----------------------------------------------------------------------
 *  | Description:   ThinkUserLogin jwt类
 *  +----------------------------------------------------------------------
 **/

namespace Hahadu\ThinkUserLogin\Builder;
use Hahadu\Helper\StringHelper;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use think\Exception;
use think\facade\Config;

class JWTBuilder
{
    protected $builder;
    protected $parser;
    protected $validate;
    protected $host;
    protected $time;
    protected $hash_key;
    protected $userid;
    private $jti;
    private $exp;
    private $nbf;
    protected $sub;
    private $alg_type = 'none';
    private $iss;
    private $aud;
    public $token;

    /****
     * JWTBuilder constructor.
     * @param int|string $userid 用户id 必须
     * @param string $hash_key 加密字符串 必须 使用用户数据库中加密后的密码，如果密码更改则token失效
     * @param array $payloads payloads 的其他信息
     */
    public function __construct($userid, $hash_key, $payloads=[]){
        $this->getConfigure();
        $this->userid = $userid;
        $this->hash_key = $hash_key;
        $this->sub = StringHelper::password($hash_key."+".$userid);
        $this->builder = new  Builder();
        $this->parser = new Parser();
        $this->validate = new ValidationData();
        $this->token = $this->create_token($payloads);
        $this->iss = !empty($this->iss)?$this->iss:$this->host;
        $this->aud = !empty($this->aud)?$this->iss:$this->host;
    }

    /****
     * @param array $payloads
     * @return \Lcobucci\JWT\Token
     */
    private function create_token($payloads=[]){
        $token =  $this->builder

            ->issuedBy($this->iss) // 令牌签发者 (iss claim)
            ->permittedFor($this->aud) // 令牌接收者 (aud claim)
            ->relatedTo($this->sub , true) //sub
            ->identifiedBy($this->jti, true) // Jwt唯一标识(jti claim),
            ->withHeader('alg',$this->alg_type)
            ->issuedAt($this->time) // jwt的签发时间 (iat claim)
            ->canOnlyBeUsedAfter($this->time + $this->nbf) //配置令牌可以使用的时间，在这个时间段前令牌不可用 (nbf claim)
            ->expiresAt($this->time + $this->exp); // jwt的过期时间，这个过期时间必须要大于签发时间 (exp claim)
        if(!empty($payloads)){
            foreach ($payloads as $k=>$v) {
                $token->withClaim($k,$v);
            }
        }
        return $token->getToken();
    }
    protected function getConfigure(){
        $this->jti = StringHelper::create_rand_string(9);
        $this->host = request()->host();
        $this->alg_type = Config::get('login.JWT.alg');
        $this->time = time();
        $this->nbf = Config::get('login.JWT.nbf');
        $this->exp = Config::get('login.JWT.exp');
        $this->iss = Config::get('login.JWT.iss');
        $this->aud = Config::get('login.JWT.aud');
    }
    public static function parser($token){
        try{
            $parser = new Parser();
            $result =  $parser->parse((string) $token);
        }catch (\InvalidArgumentException $e){
            $result = 420115; //没有检测到jwt数据，或者格式错误 ，jwt字符串必须带两个点
        }
        return $result;
    }

    /****
     * @param $token
     * @return bool|int
     */
    public function jwt_check($token){
        $token = $this->parser->parse((string) $token); // Parses from a string
        $data = $this->validate; // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($this->iss);
        $data->setAudience($this->aud);
        $jti = $token->getHeader('jti');
        $data->setId($jti);
        $data->setAudience($this->host);
        $sub=$token->getClaim('sub');
        if(!StringHelper::password($this->hash_key."+".$this->userid,$sub)){
            return 420114; //用户验证失败，请确认是您本人操作
        }
        $data->setSubject($sub);
        return $token->validate($data); // bool
    }


}