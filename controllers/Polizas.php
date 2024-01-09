<?php

require_once 'config/Conexion.php';
require_once 'controllers/Respuestas.php';
require_once 'controllers/Auth.php';

class Polizas extends Conexion {

    private $table = 'contrato';

    //Contratante de la poliza (el cliente que la va a comprar)
    private $id_contratante = '';
    private $cod_documento = '';
    //private $cod_surcusal = '1'; //Principal Caracas (esta mal escrito en la bd)
    private $id_estado = '0'; //por asignar
    private $id_ciudad = '0';
    private $id_municipio = '0';
    private $id_ocupacion = ''; //nullable
    private $id_actividad_eco = ''; //nullable
    private $datos_contratante = ''; //Es el nombre del contratante
	private $direc = '';
	private $cedula_rif = '';
	private $telef1 = '';
	private $telef2 = '00000000000';
	private $email = '';
	private $email_contreembolso = ''; //nullable
	private $zona_postal = ''; //nullable
	//private $status = 1; //Creo que este es activo

    //Plan (configuracion_plan)
    private $cod_detConfContrato = ''; //id del plan para traerme el resto de la informacion
    //private $fecha_desde = date('Y-m-d');

    //Versiones_plan
    private $id_version = '';
	private $cod_detconfcontrato = '';
	private $id_moneda = '';
	private $id_tipo_agotamiento = '';
	private $num_ren = '';
	private $num_version = '';
	private $tipo_prima = '';
	private $dias_declarar = '';
	private $comision_inter = '';
	private $comision_sm = '';
	private $desde = '';
	private $hasta = '';
	private $fecha_efectiva = '';
	//private $status = '';
	private $alias = '';
	private $fec_registro = '';

    //Plan contratante
    private $id_plan_contratante = '';
	//private $cod_detconfcontrato = ''; //id del plan
	//private $id_contratante = ''; //id del nuevo contratante
	private $status_plan_contratante = '1'; // 1: poliza emitida, 2: poliza no emitida 
	private $status = '1';
	//private $fec_registro = ''; //Fecha de hoy

    //Poliza (se llenaran cuando se cree la nueva poliza)
    private $id_contrato = '';
    private $estatus_poliza = ''; // 1: val, 2: act, 3: mod, 4: ren, 5: anu, 6: web
    private $num_contrato = '';
    //private $fec_registro = ''; // fecha de hoy

    //Versiones contrato
    private $id_version_cont = '';
	//private $num_ren = '0'; //Poliza no renovada, apenas se esta creando
	//private $id_contrato = '';
	//private $id_plan_contratante = '';
	private $cod_intermediario = ''; //El id del tipo que quiere vender las polizas
	private $cod_sucursal = 1; //Principal Caracas
	private $fecdesde = ''; //fecha de hoy
	private $fechasta = ''; //Un año despues de la fecha desde
	private $cod_tipo_forma_pago = ''; //1: anual, 2: mensual, 3: bimestral, 4: trimestral, 5: cuatrimestral, 6: semestral
	private $prima = 'UNICA'; //creo
	private $sobregiro = ''; //nullable
	private $estatus = ''; // 1: activo, 2: cerrado, 3: editable

    

    

    /* Ejemplo:
    {
        "document_id" : "123456", "sex" : "M" ,
        "type_fee" : "1",
        "sum_assured" : "15" ,
        "country" : "VE" ,
        "names" : "Luis enrique" ,
        "surnames" : "Perez" ,
        "birth_date" : "1990-09-10",
        "id_insurance" : "3",
        "email" : "prueba@lamundial.com",
        "phones" : "1234567820",
        "transaction_code" : "5214",
        "transaction_result" : "Realizada",
        "transaction_id" : "123458",
        "transaction_msg" : "Realizada",
        "transaction_ammount" : "12.00",
        "transaction_currency" : "USD"
    } */








    //Obtener la lista de los pacientes
    // public function listPacientes($page = 1) {
    //     $inicio = 0;
    //     $cantidad = 100;

    //     if ( $page > 1 ) {
    //         $inicio = $cantidad * ($page -1) + 1;
    //         $cantidad = $cantidad * $page;
    //     }

    //     $sql = "SELECT PacienteId, Nombre, FechaNacimiento, Direccion, Telefono, Correo FROM $this->table LIMIT $inicio,$cantidad;";
    //     $datos = parent::obtenerDatos($sql);

    //     return $datos;        

    // }

    // //funcion para obtener solo un paciente (luego sera poliza)
    // public function getPaciente($id) {
    //     $sql = "SELECT * FROM $this->table WHERE PacienteId = $id;";
    //     $datos = parent::obtenerDatos($sql);

    //     return $datos;
    // }


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
                    !isset($datos["fecha_hasta"])
                ) {
                    
                    return $_Respuestas->error_400();

                } else {
                    
                    //Recoger campos requeridos
                    $this->cod_documento = $datos['cod_documento'];
                    $this->cedula_rif = $datos['cedula_rif'];
                    $this->datos_contratante = $datos['nombre'];
                    $this->email = $datos['email'];
                    $this->telef1 = $datos['telefono'];
                    $this->direc = $datos['direccion'];
                    $this->fecdesde = $datos['fecha_desde'];
                    $this->fechasta = $datos['fecha_hasta'];

                    // return [
                    //     $this->cod_documento,
                    //     $this->cedula_rif,
                    //     $this->datos_contratante,
                    //     $this->email,
                    //     $this->telef1,
                    //     $this->direc,
                    //     $this->fecdesde,
                    //     $this->fechasta
                    // ];

                    // //Campos opcionales
                    // $this->codigo_postal = isset($datos['codigo_postal']) ? $datos['codigo_postal'] : $this->codigo_postal;
                    // $this->genero = isset($datos['genero']) ? $datos['genero'] : $this->genero;
                    // $this->fecha_nacimiento = isset($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : $this->fecha_nacimiento;
                    // $this->correo = isset($datos['correo']) ? $datos['correo'] : $this->correo;

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
    //proveedor, datos, emision poliza, correo_persona



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
                return ["Creando la poliza"];



            } else {
                //Tiene poliza
                return $_Respuestas->error_200("El contratante ya tiene una póliza activa");
            }



        } else {
            // return ['No existe, registrar'];
            return [$this->saveContratante()];
            //registrar ese contratante
            
        }



        // $sql = "INSERT INTO $this->table (DNI, Nombre, Direccion, CodigoPostal, Telefono, Genero, FechaNacimiento, Correo) VALUES ('$this->dni', '$this->nombre', '$this->direccion', '$this->codigo_postal', '$this->telefono', '$this->genero', '$this->fecha_nacimiento', '$this->correo');";

        // $result = parent::insertedId($sql);

        // if($result){
        //     return $result;
        // } else {
        //     return false;
        // }

    }


    private function saveContratante() {
        $sql = "INSERT INTO contratante (cod_documento, cod_surcusal, id_estado, id_ciudad, id_municipio, datos_contratante, direc, cedula_rif, telef1, telef2, email, status) VALUES ('$this->cod_documento', '$this->cod_sucursal', '$this->id_estado', '$this->id_ciudad', '$this->id_municipio', '$this->datos_contratante', '$this->direc', '$this->cedula_rif', '$this->telef1', '$this->telef2', '$this->email', '1' );";

        return $sql;
    }


    private function savePlanContratante() {

    }


}