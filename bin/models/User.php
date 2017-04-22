<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   User.php User model
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\models;

use bin\models\mysql\{Mysql, SessionManager};
use bin\services\Emailing;
use bin\models\Node;
use bin\log\Log;

/**
* To do the interface with the Mysql sub-service for user management
*
*/
class User {

     /**
     * @var Object Mysqli connect
     *
     */
     private $_mysql;

     public function __construct ()
     {
         $this->_mysql = Mysql::getInstance();
     }

     /**
     * @param $login
     * @param $email
     * @param $password password
     * @param $roles (Admin, Owner folder, Read, Write)
     */
     public function register (string $login, string $email, string $password, string $roles = "0111")
     : array
     {
         if(strlen($password) < 6) {
            return ['success' => false, 'message' => "Your password is too short"];
        }
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => "The email format is bad"];
        }
        $token = md5(uniqid());
        $this->_mysql->setUser(true);
        if(($id = $this->_mysql->setDBDatas(
                "users",
                "(login, password, pp,  email, API_key, roles, emailToken, forgotpwd, creationDate) VALUE (?, ?, ?, ?, ?, ?, ?, ?,NOW())",
                [$login, $this->_hashPassword($password), "../var/img/default.png", $email, $token, $roles, "", ""]
            ))
        ) {
            SessionManager::setSession($token, $roles, $id);

            //create the users node
            $node = new Node();
            $new = $node->initUserFolder();
            if($new['success']) {
                return ['success' => true, 'result' => ['crypt' => $this->createCookie(), 'path' => $new['result']['path'], 'nodeId' => $new['result']['nodeId']] ];
            }
            return ['success' => false, 'message' => $new['message']];

        }
        return ['success' => false, 'message' => "The email or name is still using"];
     }

     /**
     * @param $login
     * @param $password
     * @param $type
     */
     public function login (string $login, string $password, string $type)
     : array
     {
        $this->_mysql->setUser(true);

        $dataSet = ($type == 'email') ? $this->_mysql->getDBDatas(
            "SELECT login, pp, password, API_key, roles, id, valid FROM users WHERE email = ?",
            [$login]
        )->toObject() : $this->_mysql->getDBDatas(
            "SELECT login, pp, password, API_key, roles, id, valid FROM users WHERE login = ?",
            [$login]
        )->toObject();

        if($dataSet['success']) {
            $result = $dataSet['result'];
            if($this->_checkPassword($password, $result->password)) {
              SessionManager::setSession($result->API_key, $result->roles, $result->id);

              return ['success' => true, 'result' => [ 'name' => $result->login, 'pp' => $result->pp, 'crypt' => $this->createCookie()]];
            } else {
              return ['success' => false, 'message' => "Authentification error"];
            }
        }
        return ['success' => false];
     }

     public function disconnect ()
     : array
     {
         SessionManager::unsetSession();
         return ['success' => true];
     }

     public function checkUser (string $cookie)
     : array
     {
         if(!empty($cookie)) {
             return $this->decryptCookie($cookie);
         }
         return $this->_mysql->getCurrentUser();
     }

     public function updateProfil(\stdClass $request, \PHPMailer\PHPMailer\PHPMailer $mailer)
     : array
     {
         if(
             !is_string($request->passwordOld) ||
             !is_string($request->passwordNew) ||
             !is_string($request->login) ||
             !is_string($request->email)
         ) {
             return ['success' => false, 'message' => "The parameters request are wrong"];
         }
        /**
        *   Check ancien mot de passe & email
        */
         if(empty($request->passwordOld)) {
             return ['success' => false, 'message' => "The password is empty"];
         } else  {
             $dataset = $this->_mysql->getDBDatas('SELECT password, email FROM users WHERE id = ?', [SessionManager::getSession()['id']])->toObject();
             $result = $dataset['result'];
             if(!$this->_checkPassword($request->passwordOld, $result->password)) {
                 return ['success' => false, 'message' => "Your old password is wrong"];
             }


         }

         if(strlen($request->passwordNew) < 6 && !empty($request->passwordNew))
            return ['success' => false, 'message' => "Your new password is too short"];
         $password = empty($request->passwordNew) ? $request->passwordOld : $request->passwordNew;

         if($this->_mysql->updateDBDatas(
                 "users",
                 "login = ?, password = ?, email = ? WHERE id = ?",
                 [$request->login, $this->_hashPassword($password), $request->email, SessionManager::getSession()['id']]
             )
         ) {
           /*
           * Si nouvel email différent de l'ancien, relancer procédure de validation
           */
            if($request->email != $result->email) {
                $emailing = new Emailing($mailer);
                return $emailing->sendAsyncEmail($request->email, $request->login, 1);
            }
            return ['success' => true];
         }
         return ['success' => false, 'message' => "The email or name is still using"];
     }

     public function setPProfil(string $path)
     : array
     {
            //get old path
            $oldPath = $this->_mysql->getDBDatas(
                "SELECT pp FROM users WHERE id = ?", [SessionManager::getSession()['id']]
            )->toObject()['result']->pp;
            $node = new Node();
            //unset old node
            if($oldPath != 'default.png') {
                $node->unsetNode($node->getNodeFromPath($oldPath)['result']->node_ID);
            }
            //Set new node
            return $this->_mysql->updateDBDatas(
                     "users", "pp = ? WHERE id = ?", [$path, SessionManager::getSession()['id']]
                 ) ? ['success' => true]
                 : ['success' => false, 'message' => "The update of the profile picture failed"];
     }

     public function confirmEmail(string $token)
     : array
     {
         $dbToken = $this->_mysql->getDBDatas(
             "SELECT emailToken FROM users WHERE emailToken = ?", [$token]
         )->toObject()['result'];
         if(empty($dbToken) || empty($token)) {
             return ['success' => false, 'message' => "Profil activation error"];
         }
         if($dbToken->emailToken === $token) {
             $update = $this->_mysql->updateDBDatas(
                      "users", "valid = ? WHERE emailToken = ?", [1, $token]
              );
              if($update) {
                  return ['success' => true];
              }
            Log::user("Erreur activation du compte, token ok: {token}", ['token' => $token]);
            return ['success' => false, 'message' => "Profil activation error"];


         }
         return ['success' => false, 'message' => "Profil activation error"];
     }

     public function createNewPassword(string $token, string $password)
     : array
     {
        if(strlen($password) < 6) {
            return ['success' => false, 'message' => "Your new password is too short"];
        }
        $dbToken =$this->_mysql->getDBDatas(
            "SELECT emailToken FROM users WHERE forgotpwd = ?", [$token]
        )->toObject()['result'];
        if(empty($dbToken) || empty($token)) {
            return ['success' => false, 'message' => "The token is not recognize"];
        }

        if($this->_mysql->updateDBDatas(
                "users",
                "password = ? WHERE forgotpwd = ?",
                [$this->_hashPassword($password), $token]
            )
        ) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => "The unique token is not good"];

     }

     private function _hashPassword(string $pwd)
     : string
     {
         //return password_hash($pwd,PASSWORD_DEFAULT);
         return hash('sha512', $pwd."NMCAECTMD");
     }

     private function _checkPassword(string $pwd, string $crypt)
     : bool
     {
         return $this->_hashPassword($pwd) == $crypt;
     }

     private function createCookie()
     : string
     {
         $session = SessionManager::getSession();
         $apiKey = $session['APITOKEN'];
         $random = uniqid();
         $public = "NMCAECTMD";

         return hash('sha512', $apiKey.$random.$public)."|".$random."|".$session['id'];

     }

     private function decryptCookie(string $cookie)
     : array
     {
         $tab = explode('|', $cookie);
         $random = $tab[1];
         $id = $tab[2];
         $crypt = $tab[0];
         $public = "NMCAECTMD";
         $current = $this->_mysql->getCurrentUser($id);
         $hash = hash('sha512', $current['apikey'].$random.$public);
         if($hash === $crypt) {
             SessionManager::setSession($current['apikey'], "0111", $id);
             return ['success' => true, 'name' => $current['login'], 'email' => $current['email'], 'pp' => $current['pp']];
         }
         return ['success' => false];

     }

 }
