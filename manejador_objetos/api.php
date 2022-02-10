<?php

// header("Content-Type:application/json");

// echo json_encode($_GET);
// return;

// echo($_SERVER['REQUEST_METHOD']);
// return;


switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        if( isset($_GET['objects']) ){
            $dirPath = dirname(__FILE__);
            $filePath = $dirPath . "\persistencia.json";
            
            // reading file
            $f = fopen($filePath, 'r');
            $contents = fread($f, filesize($filePath));
            fclose($f);

            $json_objects = json_decode($contents);

            echo(json_encode($json_objects));
        }
    break;



    case 'POST':

        if(isset($_POST['restore'])){
            $nombre_fichero = 'persistencia.json';

            if (!file_exists($nombre_fichero)) {
                $archivo = fopen($nombre_fichero, "w+b");    // Abrir el archivo, creándolo si no existe
                if( $archivo == false )     echo "Error al crear el archivo";
                else if($archivo==true){                                   
                    file_put_contents("persistencia.json", '[]'); 
                    echo "El archivo ha sido creado";
                }
                fclose($archivo);   // Cerrar el archivo
            }


            $host    = "172.29.225.74";
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
        }
        else  if(isset($_POST['replicate'])){
            $host    = "172.29.225.74";
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
            $data['accion']=$_POST['accion'];
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
            

            socket_close($socket);
        }

        else{
            $dirPath = dirname(__FILE__);
            $filePath = $dirPath . "\persistencia.json";
            $id_filePath = $dirPath . "\id_counter.txt";
            
            // reading file
            $f = fopen($filePath, 'r');
            $contents = fread($f, filesize($filePath));
            fclose($f);

            // reading id counter file
            $f = fopen($id_filePath, 'r');
            $id_contents = fread($f, filesize($filePath));
            fclose($f);
            $next_id = $id_contents + 1;


            $message = $_POST['message'];
            
            $json_objects = json_decode($contents);
            $new_json_object = json_decode($message);
            $new_json_object->id = $next_id;

    
            array_push($json_objects , $new_json_object);
            

            // writting id counter file
            $file_resource = fopen($id_filePath , 'w');
            fwrite( $file_resource , $next_id );
            fclose($file_resource);

            // writting file
            $file_resource = fopen($filePath , 'w');
            fwrite( $file_resource , json_encode($json_objects) );
            fclose($file_resource);
            echo(json_encode($json_objects));
        }
    break;



    case 'DELETE':  
        $id_to_delete = $_GET["object_id"];

        $dirPath = dirname(__FILE__);
        $filePath = $dirPath . "\persistencia.json";
        
        // reading file
        $f = fopen($filePath, 'r');
        $contents = fread($f, filesize($filePath));
        fclose($f);


        
        $json_objects = json_decode($contents);
        $new_json_objects = array();
        $found = false;
        foreach ($json_objects as $json_object) {
            if($json_object->id == $id_to_delete){
                $found = true;
            }
            else{
                array_push($new_json_objects , $json_object);
            }  
        }

        if(!$found){
            echo json_encode(array(
                "status" => "failed"
            ));
        }


        // writting file
        $file_resource = fopen($filePath , 'w');
        fwrite( $file_resource , json_encode($new_json_objects) );
        fclose($file_resource);
        echo(json_encode($new_json_objects));
    break;



}



?>