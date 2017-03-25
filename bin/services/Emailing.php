<?php

namespace bin\services;

use PHPMailer\PHPMailer\PHPMailer;
use bin\models\mysql\Mysql;
use bin\models\mongo\Mongo;

final class Emailing {

    private $_phpmailer;
    private $_token;

    public function __construct(PHPMailer $phpmailer)
    {
        $this->_phpmailer = $phpmailer;
    }

    public function sendAsyncEmail(string $email, string $login, int $action)
    : array
    {

        if($this->_generateToken($email)) {
            $body = [
                'id' => uniqid(),
                'email' => $email,
                'login' => $login,
                'action' => $action,
                'token' => $this->_token,
                'date' => time()
            ];
            $insert = [[
                'action' => 'insert', 'body' => $body
            ]];
            Mongo::getInstance()->addToBulk($insert)->execute('email');
        }
        Log::user("Erreur génération du lien, mail: {mail}, error: {error}", ['mail' => $email, 'error' => Mysql::getInstance()->error]);
        return ['success' => false, 'message' => "Erreur à la génération du lien"];

    }

    private function _generateToken(string $email)
    : bool
    {
        $this->_token = hash('sha512', uniqid().$email."NMCAECTMD");
        return Mysql::getInstance()->updateDBDatas('users', "emailToken = ? WHERE email = ?", [$token, $email]);
    }
}
