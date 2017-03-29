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
          '_TEST'
        ];
        return in_array($funct, $functWhiteList) ?
            $this->$funct($this->request) :
            json_encode(['success' => false, 'message' => "not authorized $funct"]);
    }
    private function _TEST (\stdClass $request)
    : string
    {
        $email = new Emailing($this->phpmailer);
        return json_encode($email->sendEmail('dupont.thomas70@gmail.com', 'thomas', 1));
    }

    private function _SENDCONTACT (\stdClass $request)
    : string
    {
        return $request->text;
    }

    private function _LOGIN (\stdClass $request)
    : string
    {
        $user = new User();

        return json_encode($user->login((string) $request->login, (string) $request->password));
    }

    private function _GETHOME ()
    : string
    {
        $node = new Node();
        return json_encode($node->getNodes());
    }

    private function _UPLOAD (\stdClass $request)
    : string
    {
        $new = Upload::checkFile($request->file, $request->filename, $request->params->type)->moveFile($request->params->pNodeId);
        $request->params->path = $new['result']['path'];
        $request->params->fileId = $new['result']['nodeId'];
        switch ($request->params->type) {
            case 'profil':
                $user = new User();
                if($new['success']) {
                    $user->setPProfil($new['result']['path']);
                }
                break;
            case 'create':
                $request->params->type = '$addToSet';
                if(!CrudFile::updateDocumentWithScreenshot($request->params)['success']){
                    return ['success' => false, 'message' => "erreur query"];
                }
                break;
            case 'update':
                if(!CrudFile::updateScreenshot($request->params)['success']){
                    return ['success' => false, 'message' => "erreur query"];
                }
                break;
            default:
                return ['success' => false, 'message' => "no type of operation is set"];
        }
        //[success => true, result => [path => path, nodeId => nodeId]]
        return json_encode($new);
    }



    private function _CREATEFOLDER (\stdClass $request)
    : string
    {
        $node = new Node();
        return json_encode($node->setNode($request->nodeId, $request->name, true));
    }

    private function _DELETENODE (\stdClass $request)
    : string
    {
        $node = new Node();
        return json_encode($node->unsetNode($request->nodeId));
    }

    private function _CHECKUSER ()
    : string
    {
        $user = new User();
        return json_encode($user->checkUser());
    }

    private function _REGISTER (\stdClass $request)
    : string
    {
        $user = new User();
        $createUser = $user->register(
            (string) $request->login,
            (string) $request->email,
            (string) $request->password
        );
        return $createUser['success'] ? json_encode($createUser) : "Cet utilisateur éxiste déjà";
    }

    private function _SENDEMAIL(\stdClass $request)
    : string
    {
        $email = $request->params->email;
        $login = $request->params->login;
        $type  = $request->type;
        $emailing = new Emailing($this->phpmailer);
        return json_encode($emailing->sendAsyncEmail($email, $login, $type));
    }

    private function _CONFIRMEMAIL(\stdClass $request)
    : string
    {
        $token = $request->token;
        $user = new User();
        return json_encode($user->confirmEmail($token));
    }

    private function _DISCONNECT ()
    : string
    {
        $user = new User();
        return json_encode($user->disconnect());
    }

    private function _UPDATEPROFIL (\stdClass $request)
    : string
    {
        $user = new User();
        return json_encode($user->updateProfil($request));
    }
}
