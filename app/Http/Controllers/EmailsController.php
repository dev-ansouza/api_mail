<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class EmailsController extends BaseController
{

    /**
     * Método inicial
     */
    public function index()
    {
        //Chama o método que recebe os emails
        $this->receive_emails();
    }

    /**
     * Método que recebe os emails
    */
    public function receive_emails()
    {
        //Armazena os dados da requisição
        $dados = "boss@diamonddogs.com link from zelda -- Type your email here -- alexkid@sega.com professorwhite@saymyname.com rh@teknisa.com mario@snes SONIC@SEGA.COM darth@deatchstart.net i don't have email pedro@gmail.com.br";

        //Se a array $emails não estiver vazia, chama o método filter que é responsável por retornar ua array de e-mails válidos
        if (!empty($dados)){
            $emails = $this->filter($dados);
        };

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
            $emails_validos = fopen("storage/emails/email.txt", "w");

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

    }

    /**
     * Método responsável por "enviar" os emails
     *
     * @param Request $request
     */
    public function send_emails(Request $request)
    {

        //Instância a lib do Faker
        $faker = Faker\Factory::create();

        //Verifica se o diretório "logs" existe, se não, cria
        if (!file_exists('../../../storage/logs')) {
            mkdir('../../../storage/logs', 0777, true);
        }

        //Verifica se o diretório "sent.log" existe, se não, cria
        if (!file_exists('../../../storage/logs/sent.log')) {
            mkdir('../../../storage/logs/sent.log', 0777, true);
        }

        //Verifica se o diretório "fail.log" existe, se não, cria
        if (!file_exists('../../../storage/logs/fail.log')) {
            mkdir('../../../storage/logs/fail.log', 0777, true);
        }

        //Armazena a data e hora atual
        $data_hora = date("Y-m-d H:i");

        //Armazena os dados da requisição
        $dados = $request->all();

        //Armazena a quantidade de falhas e sucesso de envio de email
        $emails_sent = '';
        $emails_fail= '';

        //Lê o dados do arquivo
        $emails = file("../../../storage/emails/emails.txt");

        //Armazena as informações dos e-mails
        $email_info = [
            ['emails' => count($emails)],
            ['emails_sent' => $emails_sent],
            ['emails_fail' => $emails_fail]
        ];

        //Passa por cada e-mail
        foreach($emails as $enderecoEmail){

            //Cria uma nova instancia de email
            $email = new Email($enderecoEmail);

            //Simula randômicamente se o email foi enviado ou não
            $email_enviado = $faker->boolean;

            //Armazena as informações do envio do e-mail;
            $dados_emails = [
                'hora' => $data_hora,
                'email' => $email,
                'assunto' => $dados['subject'],
            ];

            //Incrementa na array de informações de emails
            //Caso o email_enviado retornar true, significa que o e-mail foi enviado com sucesso, caso contrário, false...
            if($email_enviado === true) {
                $email_info['emails_sent']++;

                //Busca os dados contidos no log de emails enviados
                $dados_log = file_get_contents("../../../storage/logs/sent.log");

                //Monta a string a ser salva no log.
                $dados_log = $data_hora . "\t" . $email . "\t" . $dados['subject'] . "\n";

                //Atualiza o log de e-mails enviados com sucesso
                file_put_contents("../../../logs/sent.log", $dados_log);

            } else {
                $email_info['emails_sfail']++;

                //Busca os dados contidos no log de emails com falha no envio
                $dados_log = file_get_contents("../../../storage/logs/sent.log");

                //Monta a string a ser salva no log.
                $dados_log = $data_hora . "\t" . $email . "\t" . $dados['subject'] . "\n";

                //Atualiza o log de e-mails enviados com falha no envio
                file_put_contents("../../../logs/fail.log", $dados_log);
            }
        }

        //Retorna os resultados JSON
        return $dados_emails = json_decode(json_encode($dados_emails), true);

    }

}
