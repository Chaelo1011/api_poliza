<?php

require_once 'config/Conexion.php';
require_once 'controllers/Respuestas.php';
require_once 'controllers/Auth.php';

class Polizas extends Conexion {

    private $table = 'contrato';

    private $id_contrato = '';
    private $estatus_poliza = '';
    private $num_contrato = '';


    //Obtener la lista de los pacientes
    public function listPacientes($page = 1) {
        $inicio = 0;
        $cantidad = 100;

        if ( $page > 1 ) {
            $inicio = $cantidad * ($page -1) + 1;
            $cantidad = $cantidad * $page;
        }

        $sql = "SELECT PacienteId, Nombre, FechaNacimiento, Direccion, Telefono, Correo FROM $this->table LIMIT $inicio,$cantidad;";
        $datos = parent::obtenerDatos($sql);

        return $datos;        

    }

    //funcion para obtener solo un paciente (luego sera poliza)
    public function getPaciente($id) {
        $sql = "SELECT * FROM $this->table WHERE PacienteId = $id;";
        $datos = parent::obtenerDatos($sql);

        return $datos;
    }


    //Funcion para guardar un paciente (luego sera poliza)
    public function post($json) {
        //3b6d27fd0ec888c8552f21376a2bf307
        $_Auth = new Auth;
        $_Respuestas = new Respuestas;
        $datos = json_decode($json, true);

        if ( !isset($datos['token']) ) {
            return $_Respuestas->error_401();
        } else {
            
            $_Auth->token = $datos['token'];
            $tokenExists = $_Auth->getToken();
            
            if ( $tokenExists ) {
                // echo "El token existe";

                //Campos requeridos
                if ( !isset($datos["dni"]) || !isset($datos["nombre"]) || !isset($datos["direccion"]) || !isset($datos["telefono"]) ) {
                    
                    return $_Respuestas->error_400();

                } else {
                    
                    //Recoger campos requeridos
                    $this->dni = $datos['dni'];
                    $this->nombre = $datos['nombre'];
                    $this->direccion = $datos['direccion'];
                    $this->telefono = $datos['telefono'];

                    //Campos opcionales
                    $this->codigo_postal = isset($datos['codigo_postal']) ? $datos['codigo_postal'] : $this->codigo_postal;
                    $this->genero = isset($datos['genero']) ? $datos['genero'] : $this->genero;
                    $this->fecha_nacimiento = isset($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : $this->fecha_nacimiento;
                    $this->correo = isset($datos['correo']) ? $datos['correo'] : $this->correo;

                    //Llamar a la funcion guardar
                    $saved = $this->save();

                    //Verificar si se guardó
                    if ( $saved ) {
                        $response = $_Respuestas->response;
                        $response['result'] = ['insertedId' => $saved];
                        return $response;
                    } else {
                        return $_Respuestas->error_500("Hubo un problema al guardar el registro");
                    }

                }

            } else {
                return $_Respuestas->error_401("El token es inválido o ha caducado");
            }
        }
        
    }
    //proveedor, datos, emision poliza, correo_persona



    private function save() {
        $sql = "INSERT INTO $this->table (DNI, Nombre, Direccion, CodigoPostal, Telefono, Genero, FechaNacimiento, Correo) VALUES ('$this->dni', '$this->nombre', '$this->direccion', '$this->codigo_postal', '$this->telefono', '$this->genero', '$this->fecha_nacimiento', '$this->correo');";

        $result = parent::insertedId($sql);

        if($result){
            return $result;
        } else {
            return false;
        }

    }
}