<?php

require_once('websockets.php');
class SalaChatServer extends WebSocketServer{

    protected function process ($user, $message) {
        echo 'user sent: '.$message.PHP_EOL;
        
        $newObject = json_decode($message);
        $xmlObject = xmlrpc_encode($newObject);

        $dirPath = dirname(__FILE__);
        $filePath = $dirPath . "\persistencia.xml";
        $file_resource = fopen($filePath , 'wb');
        fwrite( $file_resource , $xmlObject );
        fclose( $file_resource );

        $this->send($user, $message);

    }
    protected function connected ($user) {
        echo 'user connected'.PHP_EOL;
    }
    protected function closed ($user) {
        echo 'user disconnected'.PHP_EOL;
    }

}


$chatServer = new SalaChatServer("localhost","9000");
try {
    $chatServer->run();
}
catch (Exception $e) {
    $chatServer->stdout($e->getMessage());
}
