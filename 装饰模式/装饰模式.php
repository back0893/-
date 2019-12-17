<?php
/**
 * 一个form表格生成器
 */


 interface Widget{
    public function html():string;
 }


 class TextInput implements Widget{
     protected $name;
     protected $value;
    public function __construct($name,$value='')
    {
        $this->name=$name;
        $this->value=$value;
    }
     public function html():string{
        return sprintf("<input type='input' name='%s' value='%s'/>",$this->name,$this->value);
     }
 }

abstract class WidgetDecorator implements Widget{
    protected $widget;
    function setWidget(Widget $widget)
    {
        $this->widget=$widget;
    }
}
 
class Labeled extends WidgetDecorator{
    protected $label;
    public function __construct($label,Widget $widget)
    {
        $this->label=$label;
        $this->setWidget($widget);
    }
    public function html():string{
        return sprintf("<b>%s</b>%s",$this->label,$this->widget->html());
    }
}
?>
<html>
<body>
<form>
<?php 
    $label=new Labeled("test",new TextInput("name",'测试'));
    echo $label->html();
?>
</form>
</body>
</html>