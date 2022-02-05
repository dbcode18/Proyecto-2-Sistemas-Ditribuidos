<?php

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $host    = "localhost";
        $port    = 20205;
        
        //Obtenemos la informacion de los objetos
        $dirPath = dirname(__FILE__);
        $filePath = $dirPath . "\persistencia.json";
            
        // reading file
        $f = fopen($filePath, 'r');
        $contents = fread($f, filesize($filePath));
        fclose($f);

        $json_objects = json_decode($contents);
        
        
        $data=array();
        $data['accion']='COMMIT';
        $data['tipo']='ReplicarObjetos';
        $data['objetos']=json_encode($json_objects);

        $dataToSend=json_encode($data);


        // create socket
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        // connect to server
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");  
        // send string to server
        socket_write($socket, $dataToSend, strlen($dataToSend)) or die("Could not send data to server\n");
        // get server response
        $result = socket_read ($socket, 1024) or die("Could not read server response\n");
        
        if($result==1) echo('Replicacion exitosa');
        else echo('Replicacion Fallida');
        // close socket
        socket_close($socket);
        break;
}



?>