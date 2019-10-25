<?php

require 'controladores/users.php';
require 'controladores/clients.php';
require 'controladores/doers.php';
require 'controladores/jobs.php';
require 'controladores/search.php';
require 'controladores/jobsDoer.php';
require 'controladores/cms.php';
require 'controladores/availability.php';
require 'controladores/topJobs.php';
require 'controladores/topDoers.php';
require 'controladores/suggestedCategories.php';
require 'controladores/jobsforyou.php';
require 'controladores/jobsforyou_search.php';
require 'controladores/notifications.php';
require 'controladores/applyJobs.php';
require 'controladores/comments.php';
require 'controladores/languages.php';
require 'controladores/verification.php';
require 'controladores/sendEmail.php';
require 'vistas/VistaXML.php';
require 'vistas/VistaJson.php';
require 'utilidades/ExcepcionApi.php';

// Constantes de estado
const ESTADO_URL_INCORRECTA = 2;
const ESTADO_EXISTENCIA_RECURSO = 3;
const ESTADO_METODO_NO_PERMITIDO = 4;

// Preparar manejo de excepciones
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'json';

switch ($formato) {
    case 'xml':
        $vista = new VistaXML();
        break;
    case 'json':
    default:
        $vista = new VistaJson();
}

set_exception_handler(function ($exception) use ($vista) {
    $cuerpo = array(
        "estado" => $exception->estado,
        "mensaje" => $exception->getMessage()
    );
    if ($exception->getCode()) {
        $vista->estado = $exception->getCode();
    } else {
        $vista->estado = 500;
    }

    $vista->imprimir($cuerpo);
}
);

// Extraer segmento de la url
if (isset($_GET['PATH_INFO']))
    $peticion = explode('/', $_GET['PATH_INFO']);
else
    throw new ExcepcionApi(ESTADO_URL_INCORRECTA, utf8_encode("No se reconoce la petición"));

// Obtener recurso
$recurso = array_shift($peticion);
$recursos_existentes = array('clients', 'doers', 'jobs', 'certificados', 'cms', 'availability', 'search', 'topJobs', 'jobsDoer', 'jobsforyou', 'suggestedCategories', 'users','jobsforyou_search','topDoers','notifications','applyJobs','comments','languages','verification','sendEmail');

// Comprobar si existe el recurso
if (!in_array($recurso, $recursos_existentes)) {
    throw new ExcepcionApi(ESTADO_EXISTENCIA_RECURSO,
        "No se reconoce el recurso al que intenta acceder");
}

$metodo = strtolower($_SERVER['REQUEST_METHOD']);

// Filtrar método
switch ($metodo) {
    case 'get':
    case 'post':
    case 'put':
    case 'delete':
        if (method_exists($recurso, $metodo)) {
            $respuesta = call_user_func(array($recurso, $metodo), $peticion);
            $vista->imprimir($respuesta);
            break;
        }
    default:
        // Método no aceptado
        $vista->estado = 405;
        $cuerpo = [
            "estado" => ESTADO_METODO_NO_PERMITIDO,
            "mensaje" => utf8_encode("Método no permitido")
        ];
        $vista->imprimir($cuerpo);

}



