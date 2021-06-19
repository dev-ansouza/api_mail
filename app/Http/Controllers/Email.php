<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Faker\Factory as Faker;

class Email extends BaseController
{

    /**
     * Faz o "envio" de email
     */
    public function send($enderecoEmail, $request)
    {

        //Instância o Fzaninotto/Faker
        $faker = Faker::create();

        //Define da fuso horário
        date_default_timezone_set('America/Sao_Paulo');

        //Armazena a data e hora atual
        $data_hora = date("d-m-Y H:i");

        //Simula randômicamente se o email foi enviado ou não
        $email_enviado = $faker->boolean;

        //Array que armazenará se houve "envio" com sucesso ou falha
        $email_status = [];

        //Incrementa na array de informações de emails
        //Caso o email_enviado retornar true, significa que o e-mail foi enviado com sucesso, caso contrário, false...
        if($email_enviado === true) {

            //Seta a variavel como true
            $email_sent = true;

            //Busca os dados contidos no log de emails enviados
            $dados_log = file_get_contents("storage/logs/sent.log");

            //Se o log estiver vazio
            if($dados_log === "") {
                //Determina a string a ser salva no log.
                $dados_log = "Data e hora: " . $data_hora . "\t" . "Endereço de e-mail: " . $enderecoEmail . "\t" . "Assunto: " . $request['subject'] . "\n";
            } else {
                //Concatena com os dados já existente no log com a string a ser salva no log.
                $dados_log = $dados_log . "Data e hora: " . $data_hora . "\t" . "Endereço de e-mail: " . $enderecoEmail . "\t" . "Assunto: " . $request['subject'] . "\n";
            }

            //Atualiza o log de e-mails enviados com sucesso
            file_put_contents("storage/logs/sent.log", $dados_log);

            //Armazena o status do envio de email com sucesso
            $email_status['sucesso'] = $email_sent;

            //Retorna se o email foi "enviado"
            return $email_status;

        } else {

            //Seta a variavel como true
            $email_fail = true;

            //Busca os dados contidos no log de emails com falha no envio
            $dados_log = file_get_contents("storage/logs/fail.log");

            if($dados_log === "") {
                //Determina a string a ser salva no log.
                $dados_log = "Data e hora: " . $data_hora . "\t" . "Endereço de e-mail: " . $enderecoEmail . "\t" . "Assunto: " . $request['subject'] . "\n";
            } else {
                //Concatena com os dados já existente no log com a string a ser salva no log.
                $dados_log = $dados_log . "Data e hora: " . $data_hora . "\t" . "Endereço de e-mail: " . $enderecoEmail . "\t" . "Assunto: " . $request['subject'] . "\n";
            }

            //Atualiza o log de e-mails enviados com falha no envio
            file_put_contents("storage/logs/fail.log", $dados_log);

            //Armazena o status do envio de email com falha
            $email_status['falha'] = $email_fail;

            //Retorna se o email não foi "enviado"
            return $email_status;
        }

    }

}
