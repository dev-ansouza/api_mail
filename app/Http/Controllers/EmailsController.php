<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Faker\Factory as Faker;

class EmailsController extends BaseController
{

    /**
     * Método que recebe os emails
     *
     * @param Request $request
    */
    public function receive_emails(Request $request)
    {

        //Armazena os dados da requisição
        $request = $request->all();

        //Se foi enviados dados na requisição
        if(!empty($request)) {

            //Armazena os dados da requisição
            $dados = $request['emails'];

            //Se a array $emails não estiver vazia, chama o método filter que é responsável por retornar ua array de e-mails válidos
            if (!empty($dados)){

                //Chama o método responsável por filtrar os dados
                $this->filter($dados);
            };
        } else {

            //Retorna uma mensagem de erro para o usuário
            print_r("Não foram enviados dados na requisição!");

        }

    }

    /**
     * Método responsável por filtrar os emails
     *
     * @params array $dados
     */
    public function filter($dados)
    {
        //Separa a string quando houver espaçamento
        $dados_separados = explode(" ", $dados);

        //Array que armazena os e-mails válidos
        $emails = [];

        //Monta uma array de e-mails válidos
        foreach($dados_separados as $dado){

            //Se o dado for um  e-mail for válido, adiciona na array de emails validos
            if (filter_var($dado, FILTER_VALIDATE_EMAIL)){

                //Verifica se a array de emails não está vazia
                if (!empty($emails)) {

                    //Passa por cada posição da array $emails e verifica se existe algum email igual ao dado a ser adicionado
                    for($i = 0; $i < count($emails); $i++){

                        //Se o dado for igual ao e-mail, significa que o e-mail já foi adicionado
                        if($dado === $emails[$i]) {

                            //e-mail adicionado
                            $email_adicionado = true;
                        } else {

                            //e-mail não adicionado
                            $email_adicionado = false;
                        }
                    }

                    //Se o e-mail não foi adicionado, adiciona-o
                    if(!$email_adicionado) {

                        //Adiciona o dado na array
                        array_push($emails, $dado);
                    }

                //Se estiver vazia adiciona o primeiro email na array
                } else {

                    //Adiciona o primeiro dado a popular a array
                    array_push($emails, $dado);
                }
            }
        }

        //Se a array de emails não está vazia...
        if (!empty($emails)) {

            //Definição do diretório de emails
            $directory_emails = "storage/emails";

            //Verifica se o diretório "emails" existe, se não, cria
            if (!file_exists($directory_emails)) {
                mkdir($directory_emails, 0777, true);
            }

            //Salva ose-mails válidos no arquivo "email.txt"
            $emails_validos = fopen("storage/emails/emails.txt", "w");

            //Salva cada e-mail válido no arquivo txt
            foreach($emails as $email){
                fwrite($emails_validos, $email . PHP_EOL);
            }

            //Fecha o arquivo
            fclose($emails_validos);

            //Chama o método para ordenação dos e-mails
            $this->sort($emails);
        }

    }

    /**
     * Método responsável por ordenar os emails
     *
     * @param array $emails
     */
    public function sort($emails)
    {
        //Define da fuso horário
        date_default_timezone_set('America/Sao_Paulo');

        //Armazena a data e hora atual
        $data_atual = date('d-m-Y');
        $hora_atual = date("H:i");

        //Modifica o formato da hora para adicioná-la no nome do arquivo txt
        $hora_atual_explode = explode(':', $hora_atual);

        //Monta a string com a data atual e a hora atual modificada
        $data_hora_atual = $data_atual . '_' . $hora_atual_explode[0] . '_' . $hora_atual_explode[1];

        //Definição do diretório de emails para salvar no arquivo de txt
        $directory_emails = "storage/emails/emails_" . $data_hora_atual . ".txt";

        //Se existem e-mails...
        if (!empty($emails)) {

            //Ordena os emails
            natcasesort($emails);

            //Salva os e-mails válidos no arquivo "email.txt"
            $emails_validos = fopen($directory_emails, "w");

            //Salva cada e-mail válido no arquivo txt
            foreach($emails as $email){
                fwrite($emails_validos, $email . PHP_EOL);
            }

            //Fecha o arquivo
            fclose($emails_validos);

        };

        //Retorna uma mensagem de sucesso para o usuário
        print_r("Emails cadastrados com sucesso!");

    }

    /**
     * Método responsável por "enviar" os emails
     *
     * @param Request $request
     */
    public function send_emails(Request $request)
    {

        //Armazena os dados da requisição
        $request = $request->all();

        //Verifica se o subject e o body foram enviados na requisição e se não estão vazios
        if(
            isset($request['subject'])
            && $request['subject'] !== ""
            && isset($request['body'])
            && $request['body'] !== ""
        ) {

            //Array que armazena os seguintes dados recebidos
            $request = [
                'subject' => $request['subject'],
                'body' => $request['body'],
            ];

            //Armazena a quantidade de falhas e sucesso de envio de email
            $emails_sent = 0;
            $emails_fail= 0;

            //Lê o dados do arquivo
            $emails = file("storage/emails/emails.txt");

            //Verifica se o diretório "logs" existe, se não, cria
            if (!file_exists('storage/logs')) {
                mkdir('storage/logs', 0777, true);
            }

            //Verifica se o diretório "sent.log" existe, se não, cria
            if (!file_exists('storage/logs/sent.log')) {
                $sent_log = fopen("storage/logs/sent.log", "w");
                fclose($sent_log);
            }

            //Verifica se o diretório "fail.log" existe, se não, cria
            if (!file_exists('storage/logs/fail.log')) {
                $fail_log = fopen("storage/logs/fail.log", "w");
                fclose($fail_log);
            }

            //Passa por cada e-mail
            foreach($emails as $enderecoEmail){

                //Modifica a string
                $enderecoEmail = str_replace("\r\n", "", $enderecoEmail);

                //Instancia a classe Email
                $email = new Email();

                //Chama o método send na classe email
                $dados_envio = $email->send($enderecoEmail, $request);

                //Incrementa na variável que armazena a quantidade de emails enviados com sucesso
                if(isset($dados_envio['sucesso']) && $dados_envio['sucesso'] === true){
                    $emails_sent++;
                }

                //Incrementa na variável que armazena a quantidade de emails com falha no envio
                if(isset($dados_envio['falha']) && $dados_envio['falha'] === true){
                    $emails_fail++;
                }

            }

            //Armazena as informações gerais relacionadas aos emails e seus envios
            $email_info = [
                'emails' => count($emails),
                'emails_sent' => $emails_sent,
                'emails_fail' => $emails_fail
            ];

            //Retorna os resultados JSON para o cliente
            print_r($email_info = json_decode(json_encode($email_info), true));

        } else {

            //Retorna uma mensagem de erro para o usuário
            print_r("Verifique se o subject e o body foram enviados!");

        }

    }

}
