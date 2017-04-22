<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   AjaxController.php Controller for all Ajax request
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\controllers;

use bin\models\{Node, User};
use bin\services\Upload;
use bin\services\Emailing;
use bin\services\CrudFile;

/**
* @pattern Command, VMC
*/
final class AjaxController extends Controller implements APIInterface {

    /**
    * MUST be implemented
    *
    */
    public function execute ()
    : string
    {

        $funct = "_".strtoupper($this->request->action);
        $functWhiteList = [
          '_SENDCONTACT',
          '_LOGIN',
          '_GETHOME',
          '_UPLOAD',
          '_CHECKUSER',
          '_REGISTER',
          '_DISCONNECT',
          '_DELETENODE',
          '_CREATEFOLDER',
          '_UPDATEPROFIL',
          '_SENDEMAIL',
          '_CONFIRMEMAIL',
          '_SETNEWPASSWORD',
          '_TEST'
        ];
        return in_array($funct, $functWhiteList) ?
            $this->$funct($this->request) :
            json_encode(['success' => false, 'message' => "not authorized $funct"]);
    }
    private function _TEST ()
    : string
    {
        $email = new Emailing($this->phpmailer);
        return json_encode($email->sendEmail('dupont.thomas70@gmail.com', 'thomas', 1));
    }

    private function _SENDCONTACT ()
    : string
    {
        return $this->request->text;
    }

    private function _LOGIN ()
    : string
    {
        $user = new User();

        return json_encode(
            $user->login(
                (string) $this->request->login,
                (string) $this->request->password,
                $this->request->type
            )
        );
    }

    private function _GETHOME ()
    : string
    {
        $node = new Node();
        return json_encode($node->getNodes());
    }

    private function _UPLOAD ()
    : string
    {
        $new = Upload::checkFile(
            $this->request->file,
            $this->request->filename,
            $this->request->params->type
        )->moveFile($this->request->params->pNodeId);

        $this->request->params->path = $new['result']['path'];
        $this->request->params->fileId = $new['result']['nodeId'];
        switch ($this->request->params->type) {
            case 'profil':
                $user = new User();
                if($new['success']) {
                    $user->setPProfil($new['result']['path']);
                }
                break;
            case 'create':
                $this->request->params->type = '$addToSet';
                if(!CrudFile::updateDocumentWithScreenshot($this->request->params)['success']){
                    return ['success' => false, 'message' => "query error"];
                }
                break;
            case 'update':
                if(!CrudFile::updateScreenshot($this->request->params)['success']){
                    return ['success' => false, 'message' => "query error"];
                }
                break;
            default:
                return ['success' => false, 'message' => "no type of operation is set"];
        }
        //[success => true, result => [path => path, nodeId => nodeId]]
        return json_encode($new);
    }



    private function _CREATEFOLDER ()
    : string
    {
        $node = new Node();
        return json_encode($node->setNode($this->request->nodeId, $this->request->name, true));
    }

    private function _DELETENODE ()
    : string
    {
        $node = new Node();
        return json_encode($node->unsetNode($this->request->nodeId));
    }

    private function _CHECKUSER ()
    : string
    {
        $cookie = $this->request->crypt ?? "";
        $user = new User();
        return json_encode($user->checkUser($cookie));
    }

    private function _REGISTER ()
    : string
    {
        if(
            !isset($this->request->login) ||
            !isset($this->request->email) ||
            !isset($this->request->password)
        ) {
            return json_encode(['success' => false, 'message' => "One or other fields are missing"]);
        }
        $user = new User();
        $createUser = $user->register(
            (string) $this->request->login,
            (string) $this->request->email,
            (string) $this->request->password
        );
        return $createUser['success'] ? json_encode($createUser) : $createUser['message'];
    }

    private function _SENDEMAIL()
    : string
    {
        $email = $this->request->params->email ?? "wrong";
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json_encode(['success' => false, 'message' => "The email format is bad"]);
        }
        $login = $this->request->params->login ?? "";
        $type  = $this->request->type ?? 0;
        $emailing = new Emailing($this->phpmailer);
        return json_encode($emailing->sendAsyncEmail($email, $login, $type));
    }

    private function _CONFIRMEMAIL()
    : string
    {
        $token = $this->request->token;
        $user = new User();
        return json_encode($user->confirmEmail($token));
    }

    private function _SETNEWPASSWORD()
    : string
    {
        $token = $this->request->token;
        $password = $this->request->newpwd;
        $user = new User();
        return json_encode($user->createNewPassword($token, $password));
    }

    private function _DISCONNECT ()
    : string
    {
        $user = new User();
        return json_encode($user->disconnect());
    }

    private function _UPDATEPROFIL ()
    : string
    {
        $user = new User();
        return json_encode($user->updateProfil($this->request, $this->phpmailer));
    }
}
