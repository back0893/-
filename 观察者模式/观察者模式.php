<?php
/**
 * 这是一个日志记录
 */

 abstract class Logger{
     abstract public function update($e);
 }

 class StdoutLogger extends Logger{
    public function update($e)
    {
        $output=$e->getMessage();
        file_put_contents("php://stdout",$output);
    }
 }

 class FileLogger extends Logger{
    public function update($e){
        $output=$e->getMessage();
        file_put_contents(__DIR__.'/logger.log',$output);
    }
 }


 abstract class Obserable{
     protected $servers=[];
    public function attch(Logger $logger){
        $this->servers[]=$logger;
    }
    public function notify(){
        foreach($this->servers as $server){
            $server->update($this);
        }
    }
 }


 class TestA extends Obserable{
    protected $message;
     public function update(){
         /**
          * 更新操作
          * 记录日志
          */
          $this->message="messgae被改变了";
          $this->notify();
     }
     public function getMessage(){
         return $this->message;
     }
 }


 $testa=new TestA();
 $testa->attch(new StdoutLogger());
 $testa->attch(new FileLogger());
 $testa->update();