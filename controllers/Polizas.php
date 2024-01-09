<?php

require_once 'config/Conexion.php';
require_once 'controllers/Respuestas.php';
require_once 'controllers/Auth.php';

class Polizas extends Conexion {

    private $table = 'contrato';

    //Contratante de la poliza (el cliente que la va a comprar)
    private $id_contratante = '';
    private $cod_documento = '';
    private $cod_sucursal = 1; //Principal Caracas
    private $id_estado = '0'; //por asignar
    private $id_ciudad = '0';
    private $id_municipio = '0';
    private $datos_contratante = ''; //Es el nombre del contratante
	private $direc = '';
	private $cedula_rif = '';
	private $telef1 = '';
	private $telef2 = '00000000000';
	private $email = '';

    //Plan (configuracion_plan)
    private $cod_detConfContrato = ''; //id del plan para traerme el resto de la informacion

    //Plan contratante
    private $id_plan_contratante = '';
	private $status_plan_contratante = '1'; // 1: poliza emitida, 2: poliza no emitida

    //Poliza (se llenaran cuando se cree la nueva poliza)
    private $id_contrato = '';
    private $estatus_poliza = '2'; // 1: val, 2: act, 3: mod, 4: ren, 5: anu, 6: web
    private $num_contrato = '';

    //Versiones contrato
    private $id_version_cont = '';
	private $num_ren = '0'; //Poliza no renovada, apenas se esta creando
	private $cod_intermediario = '2'; //El id del tipo que quiere vender las polizas
    //Julia Soledad Garcia del Valle
	
	private $fecdesde = ''; //fecha de hoy
	private $fechasta = ''; //Un año despues de la fecha desde
	private $cod_tipo_forma_pago = ''; //1: anual, 2: mensual, 3: bimestral, 4: trimestral, 5: cuatrimestral, 6: semestral
	private $prima = 'UNICA'; //creo
	private $estatus_version = '1'; // 1: activo, 2: cerrado, 3: editable



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
                if ( 
                    !isset($datos["cod_documento"]) || 
                    !isset($datos["cedula_rif"]) || 
                    !isset($datos["nombre"]) || 
                    !isset($datos["email"]) ||
                    !isset($datos["telefono"]) ||
                    !isset($datos["direccion"]) || 
                    !isset($datos["fecha_desde"]) ||
                    !isset($datos["fecha_hasta"]) ||
                    !isset($datos["forma_pago"])
                ) {
                    
                    return $_Respuestas->error_400();

                } else {
                    
                    //Recoger campos requeridos
                    $this->cod_documento = $datos['cod_documento'];
                    $this->cedula_rif = $datos['cedula_rif'];
                    $this->datos_contratante = strtoupper($datos['nombre']);
                    $this->email = $datos['email'];
                    $this->telef1 = $datos['telefono'];
                    $this->direc = $datos['direccion'];
                    $this->fecdesde = $datos['fecha_desde'];
                    $this->fechasta = $datos['fecha_hasta'];
                    $this->cod_tipo_forma_pago = $datos["forma_pago"];


                    //Llamar a la funcion guardar
                    return $this->save();

                    //Verificar si se guardó
                    // if ( $saved ) {
                    //     $response = $_Respuestas->response;
                    //     $response['result'] = ['insertedId' => $saved];
                    //     return $response;
                    // } else {
                    //     return $_Respuestas->error_500("Hubo un problema al guardar el registro");
                    // }

                }

            } else {
                return $_Respuestas->error_401("El token es inválido o ha caducado");
            }
        }
        
    }


    
    private function save() {
        $_Respuestas = new Respuestas();

        //Consultar contratante
        $sql = "SELECT id_contratante, status FROM contratante WHERE cedula_rif = $this->cedula_rif;";

        $contratanteExists = parent::obtenerDatos($sql);
        
        if ( $contratanteExists[0] ) {

            //Existe el contratante, obtengo su id
            $this->id_contratante = $contratanteExists[0]["id_contratante"];
            //obtengo su estatus
            $status = $contratanteExists[0]["status"];

            //Verificar si ese contratante esta desactivado
            if ( $status == 2 ) { // estatus 2: desactivado
                //Actualizalo
                $query = "UPDATE contratante SET cod_documento = '$this->cod_documento', cedula_rif = '$this->cedula_rif', datos_contratante = '$this->datos_contratante', email = '$this->email', telef1 = '$this->telef1', status = '1' WHERE id_contratante = $this->id_contratante";

                $resultado = parent::affectedRows($query);

                if ( !$resultado > 0 ) {
                    return $_Respuestas->error_500();
                }
                
            }
            
            //Verificar que el contratante no tenga una poliza
            $sql = "SELECT COUNT(id_plan_contratante) as polizas_activas FROM plan_contratante WHERE plan_contratante.id_contratante = $this->id_contratante AND status = 1;";

            $polizaExists = parent::obtenerDatos($sql);

            if ( !$polizaExists[0]['polizas_activas'] > 0 ) {
                //Crea la poliza
                return $this->savePlanContratante();
                
            } else {
                //Tiene poliza
                return $_Respuestas->error_200("El contratante ya tiene una póliza activa");
            }



        } else {
            // return ['No existe, registrar'];
            //registrar ese contratante
            $registrado = $this->saveContratante();

            if ( $registrado ) {
                //Creo la poliza
                $this->id_contratante = $registrado;

                return $this->savePlanContratante();

            } else {
                //error 500
                return $_Respuestas->error_500("No se pudo registrar el nuevo contratante");
            }
            
        }

    }


    private function saveContratante() {
        $sql = "INSERT INTO contratante (cod_documento, cod_surcusal, id_estado, id_ciudad, id_municipio, datos_contratante, direc, cedula_rif, telef1, telef2, email, status) VALUES ('$this->cod_documento', '$this->cod_sucursal', '$this->id_estado', '$this->id_ciudad', '$this->id_municipio', '$this->datos_contratante', '$this->direc', '$this->cedula_rif', '$this->telef1', '$this->telef2', '$this->email', '1' );";

        $nuevo_contratante = parent::insertedId($sql);

        if ( $nuevo_contratante > 0 ) {
            return $nuevo_contratante;
        } else {
            return false;
        }

    }


    private function savePlanContratante() {
        //CREO EL PLAN CONTRATANTE
        //Asigno el plan manualmente
        $this->cod_detConfContrato = 11; //Plan ZERO
        //Insert
        $sql = "INSERT INTO plan_contratante (cod_detconfcontrato, id_contratante, status_plan_contratante, status) VALUES ('$this->cod_detConfContrato', '$this->id_contratante', '$this->status_plan_contratante', '1');";
        
        $nuevo_plan_contratante = parent::insertedId($sql);
        
        if ( $nuevo_plan_contratante > 0 ) {
            //Guardo el nuevo id que acabo de insertar
            $this->id_plan_contratante = $nuevo_plan_contratante;

            //LUEGO CREO EL CONTRATO

            //Tengo que consultar el ultimo numero de poliza para poder asignarle uno al nuevo registro
            $sql = "SELECT num_contrato FROM contrato WHERE num_contrato is not null order by num_contrato DESC limit 1;";

            $ultimo_num = parent::obtenerDatos($sql);

            if (!$ultimo_num[0]['num_contrato']) {
                //Retorna un error 500
                return false;
            } else {
                $numero = $ultimo_num[0]['num_contrato'] + 1;
                $this->num_contrato = $numero;
            }


            //Creo el nuevo contrato
            $sql = "INSERT INTO contrato (cod_estatus_poliza, num_contrato) VALUES ('$this->estatus_poliza', '$this->num_contrato');";

            $nuevo_contrato = parent::insertedId($sql);
            
            if ( $nuevo_contrato > 0 ) {
                //Se registro el contrato, ahora creo la version del contrato

                //LUEGO CREO LA VERSION DEL CONTRATO
                $this->id_contrato = $nuevo_contrato;

                $sql = "INSERT INTO versiones_contrato(
                            num_ren,
                            id_contrato,
                            id_plan_contratante,
                            cod_intermediario,
                            cod_sucursal,
                            fecdesde,
                            fechasta,
                            cod_tipo_forma_pago,
                            prima,
                            estatus
                        )
                        VALUES(
                            '$this->num_ren',
                            '$this->id_contrato',
                            '$this->id_plan_contratante',
                            '$this->cod_intermediario',
                            '$this->cod_sucursal',
                            '$this->fecdesde',
                            '$this->fechasta',
                            '$this->cod_tipo_forma_pago',
                            '$this->prima',
                            '$this->estatus_version'
                        );";    

                $nueva_version = parent::insertedId($sql);

                if ( $nueva_version > 0 ) {
                    // Manda el correo
                    //Funcion para mandar el correo
                    return [$this->id_contratante, $nuevo_plan_contratante, $nuevo_contrato, $nueva_version];

                } else {
                    //Retorna un error 500
                    return false;
                }

            } else {
                //Retorna un error 500
                return false;
            }

        } else {
            //No se pudo guardar el nuevo plan_contratante
            //Retorna un error 500
            return false;
        }

    }


}