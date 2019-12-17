<?php
/**
 * 使用一个缓存爆出php变量
 */

 /**
  * 初始的缓存类
  */

  class VarCache{
      protected $name;
      protected $type;
      public function __construct($name,$type)
      {
          $this->name=$name;
          $this->type=$type;
      }
      public function isValid(){
        return file_exists('/cache/'.$this->name.'.php');
      }
      protected function getTemplate(){
        $template = '<?php $cached_content = ';
        switch ($this->_type) {
            case 'string':
              $template .= "'%s';";
              break;
            case 'numeric':
              $template .= '%s;';
              break;
            case 'serialize':
              $template .= "unserialize(stripslashes('%s'));";
              break;
            default:
              trigger_error('invalid cache type');
          }
      
          return $template;
      }
      public function set($value){
        switch ($this->_type) {
            case 'string':
              $content = sprintf($this->getTemplate(),str_replace("'", "\\'", $value));
              break;
            case 'serialize':
              $content = sprintf($this->getTemplate(), addslashes(serialize($value)));
              break;
            case 'numeric':
              $content = sprintf($this->getTemplate(), (float)$value);
              break;
            default:
                throw new Exception('invalid cache type');
          }
        file_put_contents('/cache/'.$this->name.'.php',$$content);
      }
      public function get(){
        return include_once('/cache/'.$this->name.'.php');
      }
  }

  /**
   * 这样的缓存类,当我需要新增其他类型的缓存时,需求2个地方.
   * 这样我可以封装成 对应的类型获得特定的封装类,
   */

   abstract class CacheWrite{
       abstract public function store($file,$var);
       public function get($name){
            return include_once('/cache/'.$name.'.php');
       }
   }

   class StringCacheWrite extends CacheWrite{
       public function store($file, $var)
       {
            $content = sprintf("<?php\n\$cached_content = '%s';",str_replace("'", "\\'", $var));
            file_put_contents($file,$content);
       }
   }

   class NumericCahceWrite extends CacheWrite{
       public function store($file, $var)
       {
            $content = sprintf("<?php\n\$cached_content = %s;",(double)$var);
            file_put_contents($file,$content);
       }
   }

   class SerializingCacheWriter  extends CacheWrite{
       public function store($file, $var)
       {
            $content = sprintf("<?php\n\$cached_content = unserialize(stripslashes('%s'));",addslashes(serialize($var)));
            file_put_contents($file,$content);
       }
   }

   /**
    * 使用策略的好处是,不用关心如果读写的具体实现,同时因为是居于接口实现的,可以任意替换,而不影响到其他
    */
   class NewVarCache{
    protected $name;
    protected $type;
    public function __construct($name,$type)
    {
        $this->name=$name;
        switch (strtolower($type)) {
            case 'string': $strategy = StringCacheWrite::class; break;
            case 'numeric': $strategy = NumericCahceWrite::class; break;
            case 'serialize':
            default: $strategy = SerializingCacheWriter::class;
          }
          $this->_type =new $strategy();
    }
    public function isValid(){
      return file_exists('/cache/'.$this->name.'.php');
    }
    public function set($value){
        $file='/cache/'.$this->name.'.php';
        $this->type->store($file,$value);
    }
    public function get(){
      return $this->type->get($this->name);
    }
}
