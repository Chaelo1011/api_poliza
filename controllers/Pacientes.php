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

        return $datos;        

    }

    //funcion para obtener solo un paciente (luego sera poliza)
    public function getPaciente($id) {
        $sql = "SELECT * FROM pacientes WHERE PacienteId = $id;";
        $datos = parent::obtenerDatos($sql);

        return $datos;
    }
}