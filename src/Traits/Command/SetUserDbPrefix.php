<?php

namespace Hahadu\ThinkUserLogin\Traits\Command;

trait SetUserDbPrefix
{
    private $db_prefix = '';

    /*****
     * 设置表前缀
     * @param string $prefix
     */
    private function setDbPrefix(){
        $this->db_prefix = env('DATABASE_PREFIX','');
    }


}