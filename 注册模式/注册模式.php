<?php

class Registry{
    private $store;
    private static $instance;

    private function __construct()
    {
        $this->store=[];
    }
    public  function get($key){
        return $this->store[$key]??null;
    }
    public  function set($key,$value){
        $this->store[$key]=$value;
    }

    public static function getInstance():self{
        if(is_null(self::$instance)){
            new self();
        }
        return self::$instance;
    }
}



/**
 * 比如,我需要访问2个不同的数据库
 * 但是我不需要为这个2个数据库实现2个不同的单例
 * 可以先生成连接,并储存在注册表中
 */

$app=Registry::getInstance();
$app->set('ep',new PDO('mysql','1','1'));
$app->set('gps',new PDO('sqlite','2','2'));

/**
 * 这样,但我需要使用gps库是不再需要传参,而是使用app->get 就能获得对应的连结
 */
