<?php

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $host    = "localhost";
        $port    = 20205;
        
        $data=array();
        $data['accion']='restaurarObjetos';
        $data['tipo']='RestaurarObjetos';

        $dataToSend=json_encode($data);

        // create socket
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        // connect to server
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");  
        // send string to server
        socket_write($socket, $dataToSend, strlen($dataToSend)) or die("Could not send data to server\n");
        // get server response
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        $bytes = file_put_contents("persistencia.json", $result); 
        print_r($result);
        // close socket
        socket_close($socket);
        break;
}



?>