<?php
declare (strict_types=1);

namespace Hahadu\ThinkUserLogin\Command;

use Hahadu\ThinkUserLogin\Traits\Command\SetUserDbPrefix;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use Hahadu\Helper\StringHelper;

/*****
 * 创建用户表
 */
class CreateUser extends Command
{
    use SetUserDbPrefix;

    private $userDbName;
    private $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0123456789';

    public function __construct()
    {
        parent::__construct();

        $this->setDbPrefix();
    }

    protected function configure()
    {
        // 指令配置
        $this->setName('创建用户模型')
            ->addArgument('ModelName', Argument::OPTIONAL, "模型名")
            ->addOption('username', null, Option::VALUE_REQUIRED, '用户名')
            ->addOption('password', null, Option::VALUE_REQUIRED, '密码')
            ->setDescription('创建用户数据表');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('userModel');
        $this->createUser($input, $output);
        $this->writeUserData($input, $output);
    }

    /*****
     * 创建用户数据表
     * @param Input $input
     * @param Output $output
     */
    private function createUser(Input $input, Output $output)
    {
        if ($input->hasArgument('ModelName')) {
            $ModelName = $input->getArgument('ModelName');
            $ModelName = $ModelName ? trim($ModelName) : 'users';
        } else {
            $ModelName = 'users';
        }
        $this->userDbName = $ModelName;
        $users = $this->db_prefix . $ModelName;
        $user_sql = <<<sql

CREATE TABLE IF NOT EXISTS  `$users` (
  `id` int(15) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码；mb_password加密',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像，相对于upload/avatar目录',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '登录邮箱',
  `email_code` varchar(60) DEFAULT NULL COMMENT '激活码',
  `phone` bigint(11) UNSIGNED DEFAULT NULL COMMENT '手机号',
  `status` tinyint(1) NOT NULL DEFAULT 2 COMMENT '用户状态 0：禁用； 1：正常 ；2：未验证',
  `register_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册时间',
  `last_login_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_login_time` int(10) UNSIGNED DEFAULT NULL COMMENT '最后登录时间',
  `delete_time` int(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

sql;

        $create = Db::query($user_sql);


        $output->writeln($users . '表创建成功' . json_encode($create, 256));

    }


    /*****
     * @param Input $input
     * @param Output $output
     * @throws \think\db\exception\DbException
     */
    private function writeUserData(Input $input, Output $output)
    {
        if ($input->hasOption('username')) {
            $username = trim($input->getOption('username'));
        } else {
            $username = 'admin';
        }
        if ($input->hasOption('password')) {
            $password = trim($input->getOption('password'));
        } else {
            $password = StringHelper::create_rand_string(8, $this->chars);
        }
        $passwordHash = StringHelper::password($password);
        $Db = Db::name($this->userDbName);
        $data['username'] = $username;
        $data['password'] = $passwordHash;
        $find = $Db->where('username', $username);
        if (!empty($find->findOrEmpty())) {
            $find->update($data);
        } else {
            $data['status'] = 1;
            Db::name($this->userDbName)->save($data);
        }

        $output->writeln('用户名：' . $username);
        $output->writeln('密码：' . $password);

    }

}
