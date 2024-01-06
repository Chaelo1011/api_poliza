<?php

require_once 'config/Conexion.php';
require_once 'controllers/Respuestas.php';

class Pacientes extends Conexion {

    public function listPacientes($page = 1) {
        $_Respuestas = new Respuestas;
        $inicio = 0;
        $cantidad = 100;

        if ( $page > 1 ) {
            $inicio = $cantidad * ($page -1) + 1;
            $cantidad = $cantidad * $page;
        }

        $sql = "SELECT Nombre, FechaNacimiento, Direccion, Telefono, Correo FROM pacientes LIMIT $inicio,$cantidad;";
        $datos = parent::obtenerDatos($sql);

        $response = $_Respuestas->response;
        $response["result"] = $datos;
        echo json_encode($response);
        

    }
}