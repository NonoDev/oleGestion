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


$app->get('/logout', function() use ($app) {
    session_destroy();
    $app->redirect($app->router()->urlFor('inicio'));
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

    $app->render('nuevo_contrato.html.twig');

})->name('nuevo_contrato');

//Página listar contratos
$app->get('/listar_contrato', function() use ($app) {

    $app->render('listar_contrato.html.twig');

})->name('listar_contrato');

//Página nuevo usuario
$app->get('/nuevo_usuario', function() use ($app) {
    if(isset($_POST['enviar'])){
        var_dump($_POST);
    }

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
    if(isset($_POST['botonLogin'])){
        $nombreUser = htmlentities($_POST['username']);
        $passUser = htmlentities($_POST['password']);
        $cons = ORM::for_table('usuario')
            ->where('nombre_usuario',$nombreUser)
            ->where('password', $passUser)
            ->find_one();
        if($cons){
            $_SESSION['usuarioLogin'] = $cons;
            $app->render('inicio.html.twig');
        }else{
            $app->render('login.html.twig',array('errorLogin' => 'ok'));
        }
    }
        // REGISTRO USUARIOS
    if(isset($_POST['enviar'])){
        $registros = array();
        $registros = $_POST;
        array_pop($registros);
        $error = "";
        $ok = "";
        $check = false;
        $cons = ORM::for_table('usuario')
            ->where('nombre_usuario',$registros['usuario'])
            ->find_one();

        if($registros['password'] != $registros['password2']){
            $error = "Las contraseñas no coinciden";
            $check = false;
            $app->render('nuevo_usuario.html.twig',array(
                'mensajeError' => $error
            ));
            die();
        }else{
            $check = true;
        }
        if($registros['email'] == $registros['email2']){
            $error = "Los emails no pueden coincidir";
            $check = false;
            $app->render('nuevo_usuario.html.twig',array(
                'mensajeError' => $error
            ));
            die();
        }else{
            $check = true;
        }

        if($cons){
            $error = "Ya existe un usuario registrado con ese nombre";
            $check = false;
            $app->render('nuevo_usuario.html.twig',array(
                'mensajeError' => $error
            ));
            die();
        }else{
            $check = true;
        }

        if($check){
            $usuario = ORM::for_table('usuario')->create();

            $usuario->nombre_usuario = $registros['usuario'];
            $usuario->password = $registros['password'];
            $usuario->nombre = $registros['nombre'];
            $usuario->apellidos = $registros['apellidos'];
            $usuario->direccion = $registros['direccion'];
            $usuario->localidad = $registros['localidad'];
            $usuario->provincia = $registros['provincia'];
            $usuario->cod_postal = $registros['cpostal'];
            $usuario->telefono = $registros['telefono'];
            $usuario->movil = $registros['movil'];
            $usuario->email = $registros['email'];
            $usuario->email_secundario = $registros['email2'];
            $usuario->rol = $_POST['rol'];
            $usuario->save();

            $ok = "Usuario registrado correctamente";

        }else{
            $error = "Ha habido un fallo al registrar el usuario";
        }
        $app->render('nuevo_usuario.html.twig',array(
            'mensajeError' => $error,
            'mensajeOk' => $ok,
            'datos' => $registros
            ));
    }
});

$app->run();