<?php
class Subject {
    public function getUser(){
        return ['id'=>1,'name'=>'tom'];
    }
}
class ProxySubject {
    protected $subject;
    public function __construct()
    {
        $this->subject=new Subject();
    }
    public function  getUser(){
        return $this->subject->getUser();
    }
}
