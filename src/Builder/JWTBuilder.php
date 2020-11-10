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

class JWTBuilder
{
    protected $builder;
    protected $parser;
    protected $validate;
    protected $host;
    protected $time;
    protected $username;
    protected $userid;
    private $jti;
    private $exp;
    private $nbf;
    protected $sub;
    private $alg_type = 'none';
    public $token;

    /****
     * JWTBuilder constructor.
     * @param string $username 用户名 必须
     * @param string $userid 用户id 必须
     * @param array $payloads payloads 的其他信息
     */
    public function __construct($username,$userid,$payloads=[]){
        $this->getConfigure();
        $this->userid = $userid;
        $this->username = $username;
        $this->sub = StringHelper::password($username."+".$userid);
        $this->builder = new  Builder();
        $this->parser = new Parser();
        $this->validate = new ValidationData();
        $this->token = $this->create_token($payloads);
    }

    /****
     * @param array $payloads
     * @return \Lcobucci\JWT\Token
     */
    private function create_token($payloads=[]){
        $token =  $this->builder
            ->issuedBy(request()->server('http_host')) // 令牌签发者 (iss claim)
            ->permittedFor(request()->server('http_host')) // 令牌接收者 (aud claim)
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
        $this->alg_type = 'HS256';
        $this->time = time();
        $this->nbf = 60;
        $this->exp = 3600;
    }
    public static function parser($token){
        $parser = new Parser();
        return $parser->parse((string) $token);
    }

    /****
     * @param $token
     * @return bool|int
     */
    public function jwt_check($token){
        $token = $this->parser->parse((string) $token); // Parses from a string
        $data = $this->validate; // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer(request()->host());
        $data->setAudience(request()->host());
        $jti = $token->getHeader('jti');
        $data->setId($jti);
        $data->setAudience($this->host);
        $sub=$token->getClaim('sub');
        if(!StringHelper::password($this->username."+".$this->userid,$sub)){
            return 420113;
        }
        $data->setSubject($sub);
        return $token->validate($data); // bool
    }

}