<?php

namespace classes;

use MainClass;
use services\{Mongo, Log};


final class Emailing extends MainClass {

    public function execute(array $args)
    : void
    {
        $results = Mongo::getInstance()->createQuery([])->execute("email");
        $insert = [];
        $delete = [];
        foreach($results as $result) {
            $send = $this->sendEmail($result->email, $result->login, $result->action, $result->token);
            if($send) {
                $body = [
                    'id' => uniqid(),
                    'email' => $result->email,
                    'login' => $result->login,
                    'action' => $result->action,
                    'token' => $result->token,
                    'date' => time()
                ];
                $insert[] = [
                    'action' => 'insert', 'body' => $body
                ];

                $delete[] = [
                    'action' => 'delete', 'body' => [
                        '_id' => $result->_id
                    ]
                ];

            }
        }
        if(!empty($insert)) {
            Mongo::getInstance()->addToBulk($insert)->execute('emailHistory');
        }
        if(!empty($insert)) {
            Mongo::getInstance()->setNewBulk()->addToBulk($delete)->execute('email');
        }

    }


    public function sendEmail(string $email, string $login, int $action, string $token)
    : bool
    {
        switch ($action) {
            case 1:
                $body = $this->_getRegisterTemplate();
                $subject = "Confirmation of your email";
                break;
            case 2:
                $body = $this->_getPwdForgetTemplate();
                $subject = "Generation of a new password";
                break;
            default:
                throw new Error("not reconize action $action, emailing service", 500);
                break;
        }

        $link = $this->_generateLink($token);
        //$link = 'http://example.com';
        $body = str_replace(['{name}','{link}'] , [$login, $link], $body);
        // Expéditeur
        $this->phpmailer->SetFrom(FOOLIBADRESS, 'contact foolib');
        // Destinataire
        $this->phpmailer->AddAddress($email, $login);
        // Objet
        $this->phpmailer->Subject = $subject;
        // Votre message
        $this->phpmailer->MsgHTML($body);

        // Envoi du mail avec gestion des erreurs
        if(!$this->phpmailer->Send()) {
            Log::user("Erreur envois email confirm, mail: {mail}, error: {error}", ['mail' => $email, 'error' => $this->_phpmailer->ErrorInfo]);
            return false;
        } else {
            return true;
        }
    }

    private function _getRegisterTemplate()
    : string
    {
        return file_get_contents( ROOTDIR."../var/emailRegister.html");
    }

    private function _getPwdForgetTemplate()
    : string
    {
        return "";
    }

    private function _generateLink(string $token)
    : string
    {
        return DOMAIN."#/link?type=confirm&token=".$token;
    }
}
