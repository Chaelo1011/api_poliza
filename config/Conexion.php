<?php

class Conexion {
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $conexion;

    function __construct()
    {
        $datosConexion = $this->datosConexion();
        foreach ( $datosConexion as $dato ) {
            $this->server = $dato['server'];
            $this->user = $dato['user'];
            $this->password = $dato['password'];
            $this->database = $dato['database'];
            $this->port = $dato['port'];
        }
        $this->conexion = new mysqli($this->server, $this->user, $this->password, $this->database);

        if ( $this->conexion->connect_errno ) {
            // echo "Algo va mal con la conexion";
            echo $this->conexion->connect_error;
            die;
        }

        // try {

		// 	$conexion="mysql:host=$this->server;dbname=$this->database";
		// 	$succes= new PDO($conexion,$this->user,$this->password);
		// 	$succes->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// 	// echo "CONECTADO CORRECTAMENTE A LA BASE DE DATOS DESARROLLO<br><br>";

		// } catch(PDOException $error_rms){
		// 	echo $error_rms->getmessage();
		// }
    }


    //Funcion para obtener el json de conexion y sacar de alli los datos de conexion
    private function datosConexion() 
    {
        $direccion = dirname(__FILE__);
        //Config windows o config linux
        // $jsondata = file_get_contents($direccion. "/" . "config"); //Config windows
        $jsondata = file_get_contents($direccion. "/" . "config"); //Config linux
        return json_decode($jsondata, true);

    }

    //Funcion para convertir algun dato a UTF8 y evitarnos problemas
    private function convertirUTF8($array) {
        array_walk_recursive($array, function(&$item, $key){
            if ( !mb_detect_encoding($item, 'utf-8', true) ) {
                $item = utf8_encode($item);
            }
        });
        return $array;
    }

    //Funcion para obtener los datos solicitados de la base de datos
    public function obtenerDatos($sql){
        $results = $this->conexion->query($sql);
        $resultsArray = array();
        foreach($results as $key) {
            $resultsArray[] = $key;
        }
        return $this->convertirUTF8($resultsArray);
    }


    //Funcion que devuelve las filas afectadas por el query ejecutado
    public function affectedRows($sql){
        $results = $this->conexion->query($sql);
        return $this->conexion->affected_rows;
    }


    //Funcion que devuelve el id del ultimo registro insertado
    public function insertedId($sql){
        $results = $this->conexion->query($sql);
        $rows = $this->conexion->affected_rows;

        if ( $rows >= 1 ) {
            return $this->conexion->insert_id;
        } else {
            return 0;
        }
    }


    //Funcion para encriptar la password
    protected function encriptar($string){
        return md5($string);
    }


}

?>