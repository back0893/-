<?php

class Config{
    private static $config;
    public static function get($name){
        return self::$config[$name]??'';
    }
}
class Connecion{
    public static function getConnection(){
        return new Pdo(
            Config::get('dns'),
            Config::get('user'),
            Config::get('passwd'),
            Config::get('options')
        );
    }
} 


$connection=Connecion::getConnection();
//todo 下面可以是具体的操作