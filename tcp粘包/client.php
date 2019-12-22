$a=pack('N',2)."ab";
$client=stream_socket_client("tcp://127.0.0.1:8000");
fwrite($client,$a);
$response=fread($client,strlen($a));
echo substr($response,4);