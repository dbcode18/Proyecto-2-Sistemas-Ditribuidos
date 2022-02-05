<?php
$host = "localhost";
$port = 20205;
// No Timeout 
set_time_limit(0);

$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
$result = socket_bind($socket, $host, $port) or die("Could not bind to socket\n");
echo('server is running');
$result = socket_listen($socket, 3) or die("Could not set up socket listener\n");
do{
    $spawn = socket_accept($socket) or die("Could not accept incoming connection\n");
    $input = socket_read($spawn, 1024) or die("Could not read input\n");
    $data= json_decode($input);

    if($data->tipo=='ReplicarObjetos'){
        $replicarObjeto= new ReplicarObjetos();
        $response=$replicarObjeto->__invoke($data->accion,$data->objetos);
    }

    echo("\nResultado De a replicacion de objetos\n");
    echo($response."\n");
    socket_write($spawn, $response, strlen ($response)) or die("Could not write output\n");
}while(true);


socket_close($spawn);
socket_close($socket);


class ReplicarObjetos{
    public function __invoke($accion,$objetos){
        //print_r($accion);

        $host    = "localhost";
        $port    = 20206;
        $data=array();
        $data['accion'] = $accion;
        $data['metodo'] = 'VOTE_REQUEST';
        $data=json_encode($data);
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");  
        socket_write($socket, $data, strlen($data)) or die("Could not send data to server\n");
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        echo("\nResultado\n");
        echo($result."\n");

        if($result=='VOTE_COMMIT'){   
            //Segunda replicacion
            $response= $this->globalCommit($socket,$objetos);

        }
        socket_close($socket);
        return $response;
    }

    private function  globalCommit($socket,$objetos){
        $data=array();
        $data['objetos'] = $objetos;
        $data['metodo'] = 'GLOBAL_COMMIT';
        $data=json_encode($data);
        socket_write($socket, $data, strlen($data)) or die("Could not send data to server\n");
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        echo("\nResultado Del Global Commit\n");
        echo($result."\n");

        return $result;
    }
}

?>