<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2018/11/22
 * Time: 1:27
 * 一个简单的demo tcp服务器 php实现
 */

interface IProtocol
{
    public function getLen($raw);

    public function unpack($raw);

    public function pack($raw);
}

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

class TcpConnection
{
    protected $con;
    protected $extra_data = [];
    protected $buffer = '';
    protected $need_len;
    protected $protocol;

    public function getId(){
        return (int)$this->con;
    }
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
        $a=$this->protocol->pack($data);
        fwrite($this->con, $this->protocol->pack($data));
    }

    public function destroy()
    {
        fclose($this->con);
        return false;
    }
}

function ping($raw, TcpConnection $conn)
{
    printf("连接:%d,接口数据的长度为:%d,具体的值为:%s\n", $conn->getId(),$raw['len'], $raw['body']);
    $conn->write($raw);
}

class PServer
{
    protected $server;
    protected $conns = [];

    public function run()
    {
        $this->server = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);
        if (!$this->server) {
            exit('server up fail!');
        }
        echo 'listen' . PHP_EOL;
        $this->listen();
    }

    public function listen()
    {
        $reads = [$this->server];
        $writes = [];
        while (1) {
            $tmp_read = $reads;
            $tmp_write = $writes;
            $tmp_except = [];
            //如果没有就阻塞
            $r = stream_select($tmp_read, $tmp_write, $tmp_except, null);
            if ($r === false) {
                exit('失败!');
            }
            foreach ($tmp_read as $read) {
                if ($read === $this->server) {
                    $sock = stream_socket_accept($this->server, 5, $remote_address);
                    $fd_id = (int)$sock;
                    echo '有新的链接上来了' . $remote_address;
                    $reads[$fd_id] = $sock;
                    $this->conns[$fd_id] = new TcpConnection($sock);
                } else {
                    $fd_id = (int)$read;
                    $raw = $this->conns[$fd_id]->read();
                    if ($raw === false) {
                        unset($reads[$fd_id]);
                        continue;
                    } elseif ($raw === 0) {
                        continue;
                    }
                    ping($raw, $this->conns[$fd_id]);
                }
            }
            foreach ($tmp_except as $e) {
                echo 'error' . PHP_EOL;
            }
        }
    }
}


$server = new PServer();
$server->run();



