<?php

namespace bin\services;

use PHPMailer\PHPMailer\PHPMailer;
use bin\models\mysql\Mysql;
use bin\models\mongo\Mongo;
use bin\log\Log;

final class Emailing {

    private $_phpmailer;
    private $_token;

    const EMAILTYPE = [1,2];

    public function __construct(PHPMailer $phpmailer)
    {
        $this->_phpmailer = $phpmailer;
    }

    public function sendAsyncEmail(string $email, string $login, int $action)
    : array
    {
        if(!in_array($action, self::EMAILTYPE)) {
            return ['success' => false, 'message' => "Email type error"];
        }
        if($this->_generateToken($email, $action)) {
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
            return Mongo::getInstance()->addToBulk($insert)->execute('email');
        }
        Log::user("Erreur génération du lien, mail: {mail}, error: {error}", ['mail' => $email, 'error' => Mysql::getInstance()::$error]);
        return ['success' => false, 'message' => "Link generation error"];

    }

    private function _generateToken(string $email, int $type)
    : bool
    {
        $this->_token = hash('sha512', uniqid().$email."NMCAECTMD");
        switch ($type) {
            case 1:
                //@TODO faille de sécurité, possibilité de désactiver le compte de qqun avec sont email
                return Mysql::getInstance()->updateDBDatas('users', "emailToken = ? WHERE email = ?", [$this->_token, $email]);
                break;
            case 2:
                Mysql::getInstance()->setUser(true);
                return Mysql::getInstance()->updateDBDatas('users', "forgotpwd = ? WHERE email = ?", [$this->_token, $email]);
                break;

        }

    }
}
