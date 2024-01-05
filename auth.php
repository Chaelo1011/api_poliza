<?php

require_once 'controllers/Auth.php';
require_once 'controllers/Respuestas.php';

//Instanciar las clases que voy a necesitar
$_Auth = new Auth;
$_Respuestas = new Respuestas;


//Verificar que el metodo de autenticacion sea por POST
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    //Ejecutar la autenticacion
    //Capturo la solicitud post
    $request = file_get_contents('php://input');
    
    //Se la envio al controlador
    $datosArray = $_Auth->login($request);
    // $error = json_encode($datosArray);

    //Devuelvo una respuesta
    //Indico el tipo de respuesta
    header("Content-Type: application/json;charset=utf-8");
    //indico el codigo de respuesta (200,405,400, etc)
    http_response_code(200);
    if ( $datosArray['result']['error_id'] ) {
        $errorCode = $datosArray['result']['error_id'];
        http_response_code($errorCode);
    }
    //Imprimo el cuerpo de la respuesta
    echo json_encode($datosArray);

} else {
    header("Content-Type: application/json;charset=utf-8");
    $error = $_Respuestas->error_405();
    http_response_code($error['result']['error_id']);
    echo json_encode($error);
}