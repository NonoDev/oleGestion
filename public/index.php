<?php

include "../vendor/autoload.php";
require_once "../config.php";

$app = new \Slim\Slim(
    array(
        'view' => new \Slim\Views\Twig(),
        'templates.path' => '../templates',
        'debug' => true
    )
);

$view = $app->view();
$view->parserOptions = array(
    'charset' => 'utf-8',
    'debug' => true,
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);

# Add extensions.

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new \Twig_Extension_Debug()
);

session_cache_limiter(false);
session_start();



//Página de inicio de la aplicación
$app->get('/', function() use ($app) {
    if(isset($_SESSION['usuarioLogin'])){
        $app->render('inicio.html.twig');
    }else {
        $app->render('login.html.twig');
    }
})->name('inicio');

//Cuando pulsas en logout
$app->get('/logout', function() use ($app) {
    session_destroy();
    $app->redirect($app->router()->urlFor('inicio'));
});

//Página para las nuevas notificaciones
$app->get('/nueva_notificacion', function() use ($app) {
    if(isset($_SESSION['usuarioLogin'])){
        $app->render('inicio.html.twig');
    }else {
        $app->render('login.html.twig');
    }
});

//Página nuevas notificaciones
$app->get('/nueva_notificacion', function() use ($app) {

    $app->render('nueva_notificacion.html.twig');

})->name('nueva_notificacion');

//Página listar notificaciones
$app->get('/listar_notificacion', function() use ($app) {

    $app->render('listar_notificacion.html.twig');

})->name('listar_notificacion');

//Página nuevo contrato
$app->get('/nuevo_contrato', function() use ($app) {
    $soc = ORM::for_table('usuario')
    ->where('rol','Socio')
    ->find_many();
    $corr = ORM::for_table('usuario')
    ->where('rol','Corredor')
    ->find_many();
    $comp = ORM::for_table('usuario')
    ->where('rol','Comprador')
    ->find_many();

    $app->render('nuevo_contrato.html.twig',array('socios' => $soc, 'corredores' => $corr, 'compradores' => $comp));

})->name('nuevo_contrato');

//Página listar contratos
$app->get('/listar_contrato', function() use ($app) {

    $app->render('listar_contrato.html.twig');

})->name('listar_contrato');

//Página nuevo usuario
$app->get('/nuevo_usuario', function() use ($app) {

    $app->render('nuevo_usuario.html.twig');

})->name('nuevo_usuario');

//Página listar usuarios
$app->get('/listar_usuario', function() use ($app) {

    $usuarios = ORM::for_table('usuario')
        ->find_many();

    $app->render('listar_usuario.html.twig', [
        'usuarios' => $usuarios
    ]);

})->name('listar_usuario');

// -------------------------------------- BOTONES ------------------------------------------------

$app->post('/', function() use ($app) {
    //Al pulsar el boton de login
    if(isset($_POST['username'])){        
        $nombreUser = htmlentities($_POST['username']);
        $passUser = htmlentities($_POST['password']);
        $cons = ORM::for_table('usuario')
            ->where('nombre_usuario',$nombreUser)
            ->where('password', $passUser)
            ->find_one();        
        if($cons){
            $datetimeActual = date('Y-m-d H:i:s');
            $userAModificar = ORM::for_table('usuario')->find_one($cons['id']);
            $userAModificar->ultima_conexion = $datetimeActual;
            $userAModificar->save();

            $cons = ORM::for_table('usuario')
            ->where('nombre_usuario',$nombreUser)
            ->where('password', $passUser)
            ->find_one(); 

            $_SESSION['usuarioLogin'] = $cons;
            $app->render('inicio.html.twig');
        }else{
            $app->render('login.html.twig',array('errorLogin' => 'ok'));
        }
    }
    //Al pulsar el boton de crear nuevo contrato
    if(isset($_POST['botonCreaContrato'])){
        $num_referencia = htmlentities($_POST['ref']);
        $num_boletin = htmlentities($_POST['bol']);
        $corredor = $_POST['corr'];
        $comprador = $_POST['comp'];
        $socios = $_POST['socios'];
        $calidad = $_POST['calidad'];
        $fecha_actual=date("Y-m-d H:i:s");

        $compRef = ORM::for_table('contrato')
            ->where('referencia',$num_referencia)
            ->find_one();

        $compBol = ORM::for_table('contrato')
            ->where('boletin',$num_boletin)
            ->find_one();

        if($compRef || $compBol){
            $soc = ORM::for_table('usuario')
                ->where('rol','Socio')
                ->find_many();
            $corr = ORM::for_table('usuario')
                ->where('rol','Corredor')
                ->find_many();
            $comp = ORM::for_table('usuario')
                ->where('rol','Comprador')
                ->find_many();
            $app->render('nuevo_contrato.html.twig',array('errorContrato' => 'El número de referencia o el número de boletín ya existe','socios' => $soc, 'corredores' => $corr, 'compradores' => $comp));
            die();
        }else{
            $nuevoContrato = ORM::for_table('contrato')->create();
            $nuevoContrato->referencia = $num_referencia;
            $nuevoContrato->boletin = $num_boletin;
            $nuevoContrato->corredor_id = $corredor;
            $nuevoContrato->comprador_id = $comprador;
            $nuevoContrato->fecha_alta = $fecha_actual;
            $nuevoContrato->calidad_AOV = $calidad;
            $nuevoContrato->save();

            $consIdCont = ORM::for_table('contrato')
                ->select('id')
                ->where('referencia',$num_referencia)
                ->find_one();

            foreach($socios as $socio){
                $nuevoContratoSocios = ORM::for_table('usuario_contrato')->create();
                $nuevoContratoSocios->usuario_id = $socio;
                $nuevoContratoSocios->contrato_id = $consIdCont['id'];
                $nuevoContratoSocios->save();
            }

            $app->render('inicio.html.twig',array('nuevoContrato' => 'Contrato añadido con éxito'));
        }
    }
    
});

$app->run();