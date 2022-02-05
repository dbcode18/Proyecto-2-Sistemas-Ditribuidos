<?php
$host = "localhost";
$port = 20206;
// No Timeout 
set_time_limit(0);

$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
$result = socket_bind($socket, $host, $port) or die("Could not bind to socket\n");
echo('server is running');
$result = socket_listen($socket, 3) or die("Could not set up socket listener\n");
do{
    $spawn  = socket_accept($socket) or die("Could not accept incoming connection\n");
    $data   = socket_read($spawn, 1024) or die("Could not read input\n");
    $data   =json_decode($data);
    print_r($data);
    $accion=$data->accion;
    $metodo=$data->metodo;
    if($metodo=='VOTE_REQUEST'){
        $output='';
        if($accion=='COMMIT')       $output='VOTE_COMMIT';
        else if($accion=='ABORT')  $output='VOTE_ABORT';    
    }

    socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");

    $data = socket_read($spawn, 1024) or die("Could not read input\n");
    $data=json_decode($data);

    $output=false;
    if($data->metodo=="GLOBAL_COMMIT"){
        print_r("GLOBAL COMMIT\n");
        print_r($data);

        $objetos=$data->objetos;
        $bytes = file_put_contents("myfile.json", $objetos); 

        $output=true;
    }

    else if($data->metodo=='GLOBAL_ABORT'){
        print_r("GLOBAL ABORT\n");
        $output='false';
    }

    print_r("GLOBAL RESPONSE\n");
    print_r($output);
    socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");


   
}while(true);


socket_close($spawn);
socket_close($socket);

?>