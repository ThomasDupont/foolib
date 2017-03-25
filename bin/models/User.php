<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   User.php User model
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\models;

use bin\models\mysql\{Mysql, SessionManager};
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
     * @param $pwd password
     * @param $roles (Admin, Owner folder, Read, Write)
     */
     public function register (string $login, string $email, string $password, string $roles = "0111")
     : array
     {
        $token = md5(uniqid());
        $this->_mysql->setUser(true);
        if(($id = $this->_mysql->setDBDatas(
                "users",
                "(login, password, email, API_key, roles, creationDate) VALUE (?, ?, ?, ?, ?, NOW())",
                [$login, $this->_hashPassword($password), $email, $token, $roles]
            ))
        ) {
            SessionManager::setSession($token, $roles, $id);

            //create the users node
            $node = new Node();
            return $node->initUserFolder();
        }
        return ['success' => false, 'message' => "email ou nom déjà utilisé"];
     }

     /**
     * @param $login
     * @param $pwd
     */
     public function login (string $login, string $password)
     : array
     {
        $this->_mysql->setUser(true);
        $dataSet = $this->_mysql->getDBDatas(
            "SELECT login, pp, password, API_key, roles, id, valid FROM users WHERE login = ?",
            [$login]
        )->toObject();

        if(!$dataSet['result']->valid) {
            return ['success' => false, 'message' => "Votre email n'a pas été validé"];
        }
        if($dataSet['success']) {
            $result = $dataSet['result'];
            if($this->_checkPassword($password, $result->password)) {
              SessionManager::setSession($result->API_key, $result->roles, $result->id);

              return ['success' => true, 'result' => [ 'name' => $result->login, 'pp' => $result->pp]];
            } else {
              return ['success' => false, 'message' => "Erreur d'authentification"];
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

     public function checkUser ()
     : array
     {
         return $this->_mysql->getCurrentUser();
     }

     public function updateProfil(\stdClass $request)
     : array
     {
         if(empty($request->password)) {
             return ['success' => false, 'message' => "Le mot de passe est vide"];
         }
         if($this->_mysql->updateDBDatas(
                 "users",
                 "login = ?, password = ?, email = ? WHERE id = ?",
                 [$request->login, $this->_hashPassword($request->password), $request->email, SessionManager::getSession()['id']]
             )
         ) {
             return ['success' => true];
         }
         return ['success' => false, 'message' => "email ou nom déjà utilisé"];
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
            $node->unsetNode($node->getNodeFromPath($oldPath)['result']->node_ID);
            //Set new node
            return $this->_mysql->updateDBDatas(
                     "users", "pp = ? WHERE id = ?", [$path, SessionManager::getSession()['id']]
                 ) ? ['success' => true]
                 : ['success' => false, 'message' => "Echec de la mise à jour de la photo de profil"];
     }

     public function confirmEmail(string $token)
     : array
     {
         $dbToken = $this->_mysql->getDBDatas(
             "SELECT emailToken FROM users WHERE emailToken = ?", [$token]
         )->toObject()['result'];
         if(empty($dbToken)) {
             return ['success' => false, 'message' => "Erreur à l'activation du profil"];
         }
         if($dbToken->emailToken === $token) {
             $update = $this->_mysql->updateDBDatas(
                      "users", "valid = ? WHERE emailToken = ?", [1, $token]
              );
              if($update) {
                  return ['success' => true];
              }
            Log::user("Erreur activation du compte, token ok: {token}", ['token' => $token]);
            return ['success' => false, 'message' => "Erreur à l'activation du profil"];


         }
         return ['success' => false, 'message' => "Erreur à l'activation du profil"];
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

 }
