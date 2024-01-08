<?php

require_once 'config/Conexion.php';
require_once 'Respuestas.php';

class Auth extends Conexion {

    private $table = 'usuarios_token';
    public $token = '';
    public $token_id = '';


    public function login($json)
    {
        $_Respuestas = new Respuestas;

        $datos = json_decode($json, true);

        if ( !isset($datos['usuario']) || !isset($datos['password']) ) {
            //Error 400
            return $_Respuestas->error_400();
        } else {
            //Verificar los datos
            // return "verificando credenciales";
            $usuario = $datos['usuario'];
            $password = $datos['password'];
            $datosUsuario = $this->obtenerDatosUsuarios($usuario);

            if ($datosUsuario && $datosUsuario != 0 ) {
                // echo "Existe el usuario";
                //Encriptar la contraseña y verificar si es igual
                $passEncriptada = parent::encriptar($password);

                if ( $passEncriptada == $datosUsuario[0]["Password"] ) {
                    // echo "Usuario autenticado";
                    //Verificando que el usuario este activo
                    if ( $datosUsuario[0]["Estado"] == "Activo" ) {
                        // echo "Usuario validado";
                        //Creando el token
                        $result = $this->crearToken($datosUsuario[0]["UsuarioId"]);
                        
                        if ($result && $result !== 0) {
                            //Devolver el token
                            $response = $_Respuestas->response;
                            $response["result"] = [
                                'token' => $result
                            ];

                            return $response;

                        } else {
                            //No se pudo guardar el token
                            return $_Respuestas->error_500("Lo sentimos, tuvimos un problema el generar el token");
                        }
                        
                    } else {
                        // echo "Usuario desactivado";
                        return $_Respuestas->error_200("Lo sentimos, el usuario [[$usuario]] no se encuentra activo");
                    }

                } else {
                    // echo "Contraseña invalida";
                    return $_Respuestas->error_200("Contraseña inválida");
                }
                
            } else {
                // echo "No existe ese usuario";
                return $_Respuestas->error_200("El usuario [[$usuario]] no existe");
            }
        }
    }


    //Funcion para buscar el usuario en la base de datos y verificar su existencia
    private function obtenerDatosUsuarios($usuario)
    {
        $sql = "SELECT UsuarioId, Password, Estado FROM usuarios WHERE Usuario = '$usuario';";
        $datos = parent::obtenerDatos($sql);

        if ( $datos[0]["UsuarioId"] ) {
            return $datos;
        } else {
            return 0;
        }
    }


    //Funcion para crear el token y almacenarlo en la base de datos
    private function crearToken($userId)
    {
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $estado = "Activo";
        $date = date('Y-m-d H:i');
        $query = "INSERT INTO usuarios_token (UsuarioId, Token, Estado, Fecha) VALUES('$userId', '$token', '$estado', '$date');";

        $result = parent::affectedRows($query);
        if ( $result ) {
            return $token;
        } else {
            return 0;
        }
    }


    public function getToken()
    {
        
        $sql = "SELECT TokenId, usuarioId, Estado FROM $this->table WHERE Token = '$this->token' AND Estado = 'Activo';";

        $result = parent::obtenerDatos($sql);

        if ( $result ) {
            //Setear el token id
            $this->token_id = $result[0]["TokenId"];
            //Actualizar el token
            $this->updateToken();
            return $result;
        } else {
            return false;
        }
    }


    public function updateToken()
    {
        $date = date('Y-m-d H:i');
        $sql = "UPDATE $this->table SET Fecha = '$date' WHERE TokenId = $this->token_id;";

        return $sql;

        $result = parent::affectedRows($sql);

        if ($result >= 1) {
            return $result;
        } else {
            return false;
        }

    }
}