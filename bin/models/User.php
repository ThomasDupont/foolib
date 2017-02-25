<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   User.php User model
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\models;

use bin\models\mysql\{Mysql, SessionManager};

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
     * @param $pwd password
     * @param $roles (Admin, Owner folder, Read, Write)
     */
     public function register (string $login, string $password, string $roles = "0111")
     : array
     {
        $token = md5(uniqid());
        $this->_mysql->setUser(true);
        if(($id = $this->_mysql->setDBDatas(
                "users",
                "(login, password, API_key, roles, creationDate) VALUE (?, ?, ?, ?, NOW())",
                [$login, $password, $token, $roles]
            ))
        ) {
            SessionManager::setSession($token, $roles, $id);
            return ['success' => true];
        }
        return ['success' => false];
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
            "SELECT * FROM users WHERE login = ?",
            [$login]
        )->toObject();

        if($dataSet['success']) {
            if(password_verify($password, $dataSet['result']->password)) {
              SessionManager::setSession($dataSet['result']->API_key, $dataSet['result']->roles, $dataSet['result']->id);
              return ['success' => true, 'name' => $dataSet['result']->login];
            } else {
              return ['success' => false];
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

 }
