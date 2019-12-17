<?php
/**
 * 电子书和纸质书的关系
 */

interface IPaperBook{
    public function turnPage($page);
    public function open();
}
class PagerBook implements IPaperBook{
    public function open()
    {
        echo "打开".__CLASS__.PHP_EOL;
    }

    public function turnPage($page)
    {
        echo "跳转到".$page.'页'.PHP_EOL;
    }

}

interface IEBook{
    public function pressNext();
    public function PressStart();
}

class EBook implements IEBook{
    public function pressNext()
    {
        echo "下一章".PHP_EOL;
    }

    public function PressStart()
    {
        echo "点击开始".PHP_EOL;
    }

}


class EBookAdapter implements IPaperBook{
    private $ebook;
    public function __construct(IEBook $ebook)
    {
        $this->ebook=$ebook;
    }

    public function turnPage($page)
    {
        $this->ebook->pressNext();
    }

    public function open()
    {
        $this->ebook->PressStart();
    }

}

class Reader{
    protected $book;
    public function __construct(IPaperBook $book)
    {
        $this->book=$book;
    }
    public function read(){
        $this->book->open();
        $this->book->turnPage(1);
    }
}

$reader1=new Reader(new PagerBook());
$reader2=new Reader(new EBookAdapter(new EBook()));
$reader1->read();
$reader2->read();