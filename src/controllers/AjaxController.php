<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   AjaxController.php Controller for all Ajax request
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace src\controllers;

use src\models\User;
use src\services\Upload;
use src\services\Emailing;
use src\services\CrudFile;

/**
 * Class AjaxController
 * @package src\controllers
 */
final class AjaxController extends Controller implements APIInterface
{
    public function execute(): string
    {
        $funct = $this->request->action;
        return $this->$funct($this->request);
    }

    private function sendcontact(): string
    {
        return $this->request->text;
    }

    private function login(): string
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

    private function upload(): string
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
                if ($new['success']) {
                    $user->setPProfil($new['result']['path']);
                }
                break;
            case 'create':
                $this->request->params->type = '$addToSet';
                if (!CrudFile::updateDocumentWithScreenshot($this->request->params)['success']) {
                    return ['success' => false, 'message' => "query error"];
                }
                break;
            case 'update':
                if (!CrudFile::updateScreenshot($this->request->params)['success']) {
                    return ['success' => false, 'message' => "query error"];
                }
                break;
            default:
                return ['success' => false, 'message' => "no type of operation is set"];
        }

        return json_encode($new);
    }

    private function checkuser(): string
    {
        $cookie = $this->request->crypt ?? "";
        $user = new User();
        return json_encode($user->checkUser($cookie));
    }

    private function register(): string
    {
        if (
            !isset($this->request->login) ||
            !isset($this->request->email) ||
            !isset($this->request->password)
        ) {
            return json_encode(['success' => false, 'message' => "One or other fields are missing"]);
        }
        $user = new User();
        $createUser = $user->register(
            $this->request->login,
            $this->request->email,
            $this->request->password
        );

        Emailing::sendEmail($this->request->email, $this->request->login, 1);
        Emailing::updateDb('emailToken', $this->request->email);
        return $createUser['success'] ? json_encode($createUser) : $createUser['message'];
    }

    private function forgotpwdsendemail(): string
    {
        $email = $this->request->params->email ?? "wrong";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json_encode(['success' => false, 'message' => "The email format is bad"]);
        }
        $login = $this->request->params->login ?? "";
        Emailing::sendEmail($email, $login, 2);
        Emailing::updateDb('forgotpwd', $this->request->email);
        return json_encode([
            'success' => true
        ]);
    }

    private function confirmemail(): string
    {
        $token = $this->request->token;
        $user = new User();
        return json_encode($user->confirmEmail($token));
    }

    private function setnewpassword(): string
    {
        $token = $this->request->token;
        $password = $this->request->newpwd;
        $user = new User();
        return json_encode($user->createNewPassword($token, $password));
    }

    private function disconnect(): string
    {
        $user = new User();
        return json_encode($user->disconnect());
    }

    private function updateprofil(): string
    {
        $user = new User();
        return json_encode($user->updateProfil($this->request));
    }
}
