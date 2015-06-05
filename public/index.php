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

        $app->render('inicio.html.twig');

})->name('inicio');

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

    $app->render('nuevo_usuario.html.twig');

})->name('nuevo_usuario');

//Página listar usuarios
$app->get('/listar_usuario', function() use ($app) {

    $app->render('listar_usuario.html.twig');

})->name('listar_usuario');


$app->run();