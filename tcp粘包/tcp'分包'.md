# tcp粘包与分包

tcp本身是一个面对流的协议,是一串没有界限的数据,所以本质上的说tcp的粘包是一个不存在的问题.对于tcp的数据来说在发送的会依据嵌套字的缓冲区的实际情况来进行包的划分和发送,一个完成的数据可能会被拆解进行多次发送,也可能将多个小数据合成一个较大的数据包一次发送.

所以一般粘包和分包都是值具体的业务规定的数据结构.对于一个tcp的框架处理情况
![tcp处理](./protocol.png)


所以为了从tcp中获得传递来的数据,都会人为的规定数据构成,一般为 长度+数据 或者 使用固定的标志

定义 接口protocol
```
interface IProtocol
{
    public function getLen($raw);

    public function unpack($raw);

    public function pack($raw);
}
```
对于不同的实现,只要实现对应的数据协议就能获得数据,从而进行后续处理.


---

## 协议接口实现
实现一个协议,协议就是一个合约,对于发送人和接受人,都依据这个协议对数据流进行处理,从中提取数据
```
class Protocol implements IProtocol
{
    public function getLen($raw)
    {
        if (strlen($raw) < 4) {
            return 0;
        }
        return unpack('N', substr($raw, 0, 4))[1] + 4;
    }

    public function unpack($raw)
    {
        $data = [
            'len' => unpack('N', substr($raw, 0, 4))[1],
            'body' => substr($raw, 4)
        ];
        return $data;
    }

    public function pack($raw): string
    {
        return pack('N', $raw['len']) . $raw['body'];
    }
}
```

以上就是一个自定义的协议 固定的头长度+数据 4个字节的数据长度+数据.<br/>
也就是我规定了每一个包 都是 固定4个字节+数据 所构成.这就是分包的意义.从tcp数据流中提取需要的数据.

## 使用协议解析获得数据
```
class TcpConnection
{
    protected $con;
    protected $extra_data = [];
    protected $buffer = '';
    protected $need_len;
    protected $protocol;

    public function __construct($con)
    {
        $this->con = $con;
        $this->protocol = new Protocol();
    }

    public function read()
    {
        $buffer = fread($this->con, 10);
        if (strlen($buffer)==0) {
            if (feof($this->con)) {
                return $this->destroy();
            }
        }
        $this->buffer .= $buffer;
        if (!$this->need_len) {
            $this->need_len = $this->protocol->getLen($this->buffer);
        }
        if (strlen($this->buffer) >= $this->need_len) {
            $raw = substr($this->buffer, 0, $this->need_len);
            $this->buffer = substr($this->buffer, $this->need_len);
            $this->need_len = 0;
            $data = $this->protocol->unpack($raw);
            return $data;
        }
        return 0;
    }

    public function write($data)
    {
        fwrite($this->con, $this->protocol->pack($data));
    }

    public function destroy()
    {
        fclose($this->con);
        return false;
    }
}
```
上面一个tcp连接封装的对象,用来读取和发送数据.在读取到数据后会使用协议去解析数据,并且将数据返回给处理人,进行后续的处理.
在每次连接都读取对应的,并且接受到数值后拆解包,获得头长度,读取数据,直到对应的长度.返回用户处理具体的拆包后的数据,比如显示后返回
