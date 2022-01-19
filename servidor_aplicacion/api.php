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
        elseif( isset($_GET['restore']) ){
            echo "restaurar";
        }
        elseif( isset($_GET['replicate']) ){
            echo "replicar";
        }
        break;



    case 'POST':
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

        // echo json_encode($_GET["object_id"]);
        break; 



}



?>