<?php

//redis的单例
class RedisInstance{
    private static $instance;
    private function __construct($ip,$port,$auth='')
    {
        self::$instance=new Redis();
        $ok=self::$instance->connect($ip,$port);
        if($ok==false){
            throw new Exception("连接redis失败");
        }
        if($auth){
            $ok=self::$instance->auth($auth);
            if($ok==false){
                throw new Exception("认真redis失败");
            }
        }
    }
    public static function getInstance(){
        if(is_null(self::$instance)){
            $config=Config::get('redis');
            new self($config['ip'],$config['port'],$config['auth']??'');
        }
        return self::$instance;
    }
    private function __clone(){}
}

//配置读取的单例
class Config{
    private static $config;
    private function __construct()
    {
        self::$config=require_once __DIR__.'/config.php';
    }

    public static function get($name){
        return self::$config[$name]??'';
    }
}

//mysql连接的单例

class MysqlDb{
    private static $db;
    private function __construct($dsn,$user,$passwd,$options=[])
    {
        self::$db=new PDO($dsn,$user,$passwd,$options);
    }
    public static function getInstance(){
        if(is_null(self::$db)){
            $config=Config::get('database');
            new self($config['dsn'],$config['user'],$config['passwd'],$config['options']??[]);
        }
        return self::$db;
    }
}
