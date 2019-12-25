<?php
$a=pack('N',5);
$client=stream_socket_client("tcp://127.0.0.1:8000");
fwrite($client,$a);
sleep(1);
fwrite($client,'ababc');
$response=fread($client,5+4);
echo substr($response,4);