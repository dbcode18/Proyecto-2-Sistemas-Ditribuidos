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
        print_r('ReplicarObjetos');
        $replicarObjeto= new ReplicarObjetos();
        $response=$replicarObjeto->__invoke($data->accion,$data->objetos);
    }

    else if($data->tipo=='RestaurarObjetos'){
        print_r('RestaurarObjetos');
        $restaurarObjetos= new RestaurarObjetos();
        $response=$restaurarObjetos->__invoke($data->accion);
    }

    echo("\nResultado De a replicacion de objetos\n");
    echo($response."\n");
    socket_write($spawn, $response, strlen ($response)) or die("Could not write output\n");
}while(true);


socket_close($spawn);
socket_close($socket);


class ReplicarObjetos{
    public function __invoke($accion,$objetos){
        $data=array();
        $data['accion'] = $accion;
        $data['metodo'] = 'VOTE_REQUEST';
        $data=json_encode($data);

        //SOCKET 1
        $host    = "localhost";
        $port    = 20206;
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");  
        socket_write($socket, $data, strlen($data)) or die("Could not send data to server\n");
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        echo("\nResultado Socket 1\n");
        echo($result."\n");

        //SOCKET 2
        $host2    = "localhost";
        $port2    = 20207;
        $socket2 = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        $result2 = socket_connect($socket2, $host2, $port2) or die("Could not connect to server\n");  
        socket_write($socket2, $data, strlen($data)) or die("Could not send data to server\n");
        $result2 = socket_read ($socket2, 1024) or die("Could not read server response\n");
        echo("\nResultado Socket 2\n");
        echo($result2."\n");
        
        //Las 2 replicas estuvieron bien


        if($result=='VOTE_COMMIT' && $result2=='VOTE_COMMIT' ){   
            $response= $this->globalCommit($socket,$socket2,$objetos);
        }
        else if($result=='VOTE_ABORT'){
            echo('VOTE_ABORT del primer servidor');
            $this->globalAbort($socket,$socket2);
            $response='false';
        }

        else if($result2=='VOTE_ABORT'){
            echo('VOTE_ABORT del segundo servidor');
            $this->globalAbort($socket,$socket2);
            $response='false';
        }
        socket_close($socket);
        socket_close($socket2);

        echo("\RESPUESTA\n");
        echo($response);
        return $response;
    }

    private function  globalCommit($socket,$socket2,$objetos){
        $data=array();
        $data['objetos'] = $objetos;
        $data['metodo'] = 'GLOBAL_COMMIT';
        $data=json_encode($data);
        socket_write($socket, $data, strlen($data)) or die("Could not send data to server\n");
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        socket_write($socket2, $data, strlen($data)) or die("Could not send data to server\n");
        $result2 = socket_read ($socket2, 1024) or die("Could not read server response\n");
        echo("\Global Commit Exitoso\n");

        return true;
    }

    private function globalAbort($socket,$socket2){
        $data=array();
        $data['metodo'] = 'GLOBAL_ABORT';
        $data=json_encode($data);
        socket_write($socket, $data, strlen($data)) or die("Could not send data to server\n");
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        socket_write($socket2, $data, strlen($data)) or die("Could not send data to server\n");
        $result2 = socket_read ($socket2, 1024) or die("Could not read server response\n");
        echo("\nResultado Del Global Abort\n");
        echo($result."\n");

        return $result;

    }
}

class RestaurarObjetos{
    public function __invoke($accion){
        $data=array();
        $data['accion'] = $accion;
        $data['metodo'] = 'RecibirObjetos';
        $data=json_encode($data);
        

        //SOCKET 1
        $host    = "localhost";
        $port    = 20206;
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");  
        socket_write($socket, $data, strlen($data)) or die("Could not send data to server\n");
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        echo("\nResultado Socket 1\n");
        echo($result."\n");

        if(!empty($result)){
            socket_close($socket);
            return $result;
        }

        //SOCKET 2
        $host2    = "localhost";
        $port2    = 20207;
        $socket2 = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        $result2 = socket_connect($socket2, $host2, $port2) or die("Could not connect to server\n");  
        socket_write($socket2, $data, strlen($data)) or die("Could not send data to server\n");
        $result2 = socket_read ($socket2, 1024) or die("Could not read server response\n");
        echo("\nResultado Socket 2\n");
        echo($result2."\n");

        if(!empty($result2)){
            socket_close($socket2);
            return $result2;;
        }

        return [];
    }
}

?>