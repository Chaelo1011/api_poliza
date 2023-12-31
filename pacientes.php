<?php

require_once 'controllers/Respuestas.php';
require_once 'controllers/Pacientes.php';

$_Respuestas = new Respuestas();
$_Pacientes = new Pacientes;

$method = $_SERVER['REQUEST_METHOD'];

if ( $method == 'GET') {

    //Mostrar la lista de pacientes
    // Aqui debo recoger la variable limit para la cantidad de elementos a mostrar
    if ( isset($_GET['id']) ) {
        
        $id = $_GET['id'];
        $datos = $_Pacientes->getPaciente($id);

        $response = $_Respuestas->response;
        $response["result"] = $datos;

        header("Content-Type: application/json");
        http_response_code(200);
        echo json_encode($response);

    } else {

        //Recuperar la variable page si es que existe, sino le doy un valor null
        if ( isset($_GET['page']) ) {
            $page = $_GET['page'];
        } else {
            $page = null;
        }
        
        //No hay problema si le mando page = null, en el modelo le doy un valor por defecto de 1
        $datos = $_Pacientes->listPacientes($page);

        $response = $_Respuestas->response;
        $response["result"] = $datos;

        header("Content-Type: application/json");
        http_response_code(200);
        echo json_encode($response);
    }


} else if ( $method == 'POST') {

    // echo "Hola post";
    //Recibimos los datos enviados
    $postBody = file_get_contents('php://input');

    //Envio los datos al controlador
    $datos = $_Pacientes->post($postBody);
    
    //Devolvemos una respuesta
    //Si la respuesta contiene algun error
    if ( $datos['result']['error_msg'] ) {
        header('Content-Type: application/json');
        http_response_code($datos['result']['error_id']);
        echo json_encode($datos);
    } else {
        //Respuesta sin error
        echo json_encode($datos);
    }


} else if ( $method == 'PUT') {

    echo "Hola put";

} else if ( $method == 'DELETE') {

    echo "Hola delete";

} else {
    //La solicitud no fue realizada usando un metodo valido
    header("Content-Type: application/json;charset=utf-8");
    $error = $_Respuestas->error_405();
    http_response_code($error['result']['error_id']);
    echo json_encode($error);
}