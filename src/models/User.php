<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   User.php User model
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace src\models;

use src\models\mysql\Mysql;
use src\models\mysql\SessionManager;
use src\services\Emailing;
use src\log\Log;

/**
* To do the interface with the Mysql sub-service for user management
 *
* Class User
*/
class User
{

     /**
     * @var Object Mysqli connect
     *
     */
    private $mysql;

    public function __construct()
    {
        $this->mysql = Mysql::getInstance();
    }

    /**
    * @param $login
    * @param $email
    * @param $password password
    * @param $roles (Admin, Owner folder, Read, Write)
    */
    public function register(string $login, string $email, string $password, string $roles = "0111"): array
    {
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => "Your password is too short"];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => "The email format is bad"];
        }

        $token = md5(uniqid());
        $this->mysql->setUser(true);

        if (($id = $this->mysql->setDBDatas(
                "users",
                "(login, password, pp,  email, API_key, roles, emailToken, forgotpwd, creationDate) VALUE (?, ?, ?, ?, ?, ?, ?, ?,NOW())",
                [$login, $this->hashPassword($password), "../var/img/default.png", $email, $token, $roles, "", ""]
            ))
        ) {
            SessionManager::setSession($token, $roles, $id);

            //create the users node
            if (FILESYSTEM) {
                $node = new Node();
                $new = $node->initUserFolder();
                if (!$new['success']) {
                    return ['success' => false, 'message' => $new['message']];
                }
                return ['success' => true, 'result' => ['crypt' => $this->createCookie(), 'path' => $new['result']['path'], 'nodeId' => $new['result']['nodeId']] ];
            }
            return ['success' => true, 'result' => ['crypt' => $this->createCookie(), 'path' => 'no_path', 'nodeId' => 0]];

        }

        return ['success' => false, 'message' => "The email or name is still using"];
    }

    /**
    * @param $login
    * @param $password
    * @param $type
    */
    public function login(string $login, string $password, string $type): array
    {
        $this->mysql->setUser(true);

        $dataSet = ($type == 'email') ? $this->mysql->getDBDatas(
            "SELECT login, pp, password, API_key, roles, id, valid FROM users WHERE email = ?",
            [$login]
        )->toObject() : $this->mysql->getDBDatas(
            "SELECT login, pp, password, API_key, roles, id, valid FROM users WHERE login = ?",
            [$login]
        )->toObject();

        if ($dataSet['success']) {
            $result = $dataSet['result'];
            if ($this->checkPassword($password, $result->password)) {
                SessionManager::setSession($result->API_key, $result->roles, $result->id);

                return ['success' => true, 'result' => [ 'name' => $result->login, 'pp' => $result->pp, 'crypt' => $this->createCookie()]];
            } else {
                return ['success' => false, 'message' => "Authentification error"];
            }
        }

        return ['success' => false];
    }

    /**
     * @return array
     */
    public function disconnect(): array
    {
        SessionManager::unsetSession();

        return ['success' => true];
    }

    /**
     * @param string $cookie
     * @return array
     */
    public function checkUser(string $cookie): array
    {
        if (!empty($cookie)) {
            return $this->decryptCookie($cookie);
        }

        return $this->mysql->getCurrentUser();
    }

    /**
     * @param \stdClass $request
     * @return array
     */
    public function updateProfil(\stdClass $request): array
    {
        if (
             !is_string($request->passwordOld) ||
             !is_string($request->passwordNew) ||
             !is_string($request->login) ||
             !is_string($request->email)
         ) {
            return ['success' => false, 'message' => "The parameters request are wrong"];
        }
        /**
        *   Check old password && email
        */
        if (empty($request->passwordOld)) {
            return ['success' => false, 'message' => "The password is empty"];
        } else {
            $dataset = $this->mysql->getDBDatas('SELECT password, email FROM users WHERE id = ?', [SessionManager::getSession()['id']])->toObject();
            $result = $dataset['result'];
            if (!$this->checkPassword($request->passwordOld, $result->password)) {
                return ['success' => false, 'message' => "Your old password is wrong"];
            }
        }

        if (strlen($request->passwordNew) < 6 && !empty($request->passwordNew)) {
            return ['success' => false, 'message' => "Your new password is too short"];
        }
        $password = empty($request->passwordNew) ? $request->passwordOld : $request->passwordNew;

        if ($this->mysql->updateDBDatas(
                 "users",
                 "login = ?, password = ?, email = ? WHERE id = ?",
                 [$request->login, $this->hashPassword($password), $request->email, SessionManager::getSession()['id']]
             )
         ) {
            /*
            * If the new email is different as older, relaunch the validation process
            */
            if ($request->email != $result->email) {
                return Emailing::sendEmail($request->email, $request->login, 1);
            }
            return ['success' => true];
        }

        return ['success' => false, 'message' => "The email or name is still using"];
    }

    /**
     * @param string $path
     * @return array
     */
    public function setPProfil(string $path): array
    {
        //get old path
        $oldPath = $this->mysql->getDBDatas(
                "SELECT pp FROM users WHERE id = ?",
                [SessionManager::getSession()['id']]
            )->toObject()['result']->pp;
        $node = new Node();
        //unset old node
        if ($oldPath !== '../var/img/default.png') {
            $node->unsetNode($node->getNodeFromPath($oldPath)['result']->node_ID);
        }
        //Set new node
        return $this->mysql->updateDBDatas(
                     "users",
                "pp = ? WHERE id = ?",
                [$path, SessionManager::getSession()['id']]
                 ) ? ['success' => true]
                 : ['success' => false, 'message' => "The update of the profile picture failed"];
    }

    /**
     * @param string $token
     * @return array
     */
    public function confirmEmail(string $token): array
    {
        $dbToken = $this->mysql->getDBDatas(
             "SELECT emailToken FROM users WHERE emailToken = ?",
             [$token]
         )->toObject()['result'];

        if (empty($dbToken) || empty($token)) {
            return ['success' => false, 'message' => "Profil activation error"];
        }

        if ($dbToken->emailToken === $token) {
            $update = $this->mysql->updateDBDatas(
                      "users",
                 "valid = ? WHERE emailToken = ?",
                 [1, $token]
              );
            if ($update) {
                return ['success' => true];
            }
            Log::user("Erreur activation du compte, token ok: {token}", ['token' => $token]);

            return ['success' => false, 'message' => "Profil activation error"];
        }

        return ['success' => false, 'message' => "Profil activation error"];
    }

    /**
     * @param string $token
     * @param string $password
     * @return array
     */
    public function createNewPassword(string $token, string $password): array
    {
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => "Your new password is too short"];
        }

        $this->mysql->setUser(true);
        $dbToken = $this->mysql->getDBDatas(
            "SELECT forgotpwd FROM users WHERE forgotpwd = ?",
            [$token]
        )->toObject()['result'];

        if (!isset($dbToken->forgotpwd)) {
            return ['success' => false, 'message' => "The token is not recognize"];
        }

        if (
            empty($dbToken->forgotpwd) ||
            empty($token) ||
            $dbToken->forgotpwd != $token
        ) {
            return ['success' => false, 'message' => "The token is not recognize"];
        }

        if ($this->mysql->updateDBDatas(
                "users",
                "password = ?, forgotpwd = '' WHERE forgotpwd = ?",
                [$this->hashPassword($password), $token]
            )
        ) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => "The unique token is not good"];
    }

    /**
     * @param string $pwd
     * @return string
     */
    private function hashPassword(string $pwd): string
    {
        return hash('sha512', $pwd."NMCAECTMD");
    }

    /**
     * @param string $pwd
     * @param string $crypt
     * @return bool
     */
    private function checkPassword(string $pwd, string $crypt): bool
    {
        return $this->hashPassword($pwd) === $crypt;
    }

    /**
     * @return string
     */
    private function createCookie(): string
    {
        $session = SessionManager::getSession();
        $apiKey = $session['APITOKEN'];
        $random = uniqid();
        $public = "NMCAECTMD";

        return hash('sha512', $apiKey.$random.$public)."|".$random."|".$session['id'];
    }

    /**
     * @param string $cookie
     * @return array
     */
    private function decryptCookie(string $cookie): array
    {
        [$crypt, $random, $id] = explode('|', $cookie);
        $public = "NMCAECTMD";
        $current = (object) $this->mysql->getCurrentUser($id);
        $hash = hash('sha512', $current->apikey.$random.$public);
        if ($hash === $crypt) {
            SessionManager::setSession($current->apikey, "0111", $id);

            return ['success' => true, 'name' => $current->login, 'email' => $current->email, 'pp' => $current->pp];
        }

        return ['success' => false];
    }
}
