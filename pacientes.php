<?php

require_once 'controllers/Respuestas.php';
require_once 'controllers/Pacientes.php';

$_Respuestas = new Respuestas();
$_Pacientes = new Pacientes;

$method = $_SERVER['REQUEST_METHOD'];

if ( $method == 'GET') {

    // echo "Hola get";
    //Mostrar la lista de pacientes
    // Aqui debo recoger la variable limit para la cantidad de elementos a mostrar
    if ( isset($_GET['page']) ) {
        $page = $_GET['page'];
    } else {
        $page = null;
    }

    $_Pacientes->listPacientes($page);

    //Tambien puedo recoger un id de un paciente en especifico para mostrarlo solo a el/ella

} else if ( $method == 'POST') {

    echo "Hola post";

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