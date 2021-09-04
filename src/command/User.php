<?php
declare (strict_types = 1);

namespace Hahadu\ThinkUserLogin\Builder\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
//use think\Db;

/*****
 * 创建用户表
 */
class User extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('userModel')
            ->setDescription('the userModel command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('userModel');
        Db::execute();
    }
}
