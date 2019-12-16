<?php
class Dollar {
    protected $amount;
    public function __construct($amount)
    {
        $this->amount=$amount;
    }
    public function getAmount(){
        return $this->amount;
    }
    /**
     * 因为dollar是一个不可变的对象,
     * 当其中的值改变时应该生成一个新的对象
     */
    public function add($dollar){
        return new Dollar($this->amount+$dollar->getAmount());
    }
    public function debit($dollar){
        return new Dollar($this->amount-$dollar->getAmount());
    }
    public function __toString()
    {
        return sprintf("%s",spl_object_hash($this));
    }
}

class Monopoly{
    protected $go_amount;
    public function __construct(){
        $this->go_amount=new Dollar(200);
    }
    public function passGo($player){
        $player->collect($this->go_amount);
    }
    public function payRent($from,$to,$rent){
        $to->collect($from->pay($rent));
    }
}


class Player{
    protected $name;
    PUBLIC $saving; //这里将protected 修改成public方便查看对象的地址
    public function __construct($name)
    {
        $this->name=$name;
        $this->saving=new Dollar(1500);
    }

    public function collect($amount){
        $this->saving=$this->saving->add($amount);
    }
    public function pay($dollar){
        $this->saving=$this->saving->debit($dollar);
        return $dollar;
    }
    public function getBalance(){
        return $this->saving->getAmount();
    }
}


/**
 * 这样我们可以向一个player中新增金额
 */

 $game=new Monopoly();
 $p1=new Player('a');

 echo '初始地址'.$p1->saving.PHP_EOL;

 $game->passGo($p1);//p1的金额目前1700
 echo '新增200后'.$p1->saving.PHP_EOL;
 echo $p1->getBalance().PHP_EOL;
 $game->passGo($p1);//p1的金额目前1900
 echo '再次增加200后'.$p1->saving.PHP_EOL;
 echo $p1->getBalance().PHP_EOL;

 $p1=new Player('p1');
 $p2=new Player('p2');

 echo 'p1的初始地址'.$p1->saving.PHP_EOL;
 echo 'p2的初始地址'.$p2->saving.PHP_EOL;
 $game->payRent($p1,$p2,new Dollar(46));
 echo 'p1的修改金额地址'.$p1->saving.PHP_EOL;
 echo 'p2的修改金额地址'.$p2->saving.PHP_EOL;
 echo $p1->getBalance().PHP_EOL;
 echo $p2->getBalance().PHP_EOL;

