<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->welcome();
});

//Rota para adiocionar emails
$app->get('/emails/add', 'EmailsController@index');

//Rota para enviar emails
$app->post('/emails/send', 'EmailsController@send_emails');

