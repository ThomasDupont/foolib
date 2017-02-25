<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   Mysql.php Mysql interface full documented used as service
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\models\mysql;

use bin\log\Log;
use bin\models\mysql\SessionManager;

/**
* @pattern Singleton
*/
class Mysql {

    /**
    * @var Object Mysqli connect
    *
    */
    private static $_mysqli;

    /**
    * @var Object Mysql()
    *
    */
    private static $_instance;

    /**
    * @var Object Query result
    *
    */
    private static $_result;

    /**
    * @var Boolean
    * Used for connection and registration, false by default
    */
    public static $_user = false;

    public static function setUser(bool $bool)
    : void
    {
        self::$_user = $bool;
    }

    public static function getInstance()
    : self
    {
        if(is_null(self::$_instance)) {
             self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function __construct ()
    {
        self::$_mysqli = mysqli_init();
        try {
            if (!self::$_mysqli) {
                throw new \Exception(Log::error("mysqli_init failed"), 503);
            }
            if (!self::$_mysqli->real_connect(SQLIP, SQLUSER, SQLPWD, DATABASE, SQLPORT)) {
                throw new \Exception(Log::error("Connect Error ({errno}) {error}", ['errno' => mysqli_connect_errno(), 'error' => mysqli_connect_error()]), 503);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }

    }

    public function __destruct()
    {
        self::$_mysqli->close();
    }

    /**
    * All sql operation must be authorised
    */
    public static function getCurrentUser()
    : array
    {
        $sql = "SELECT * FROM users WHERE API_key = '".SessionManager::getSession()['APITOKEN']."'";
        $result = self::$_mysqli->query($sql);
        if($result->num_rows) {
            $dataSet = $result->fetch_array();
            $result->close();
            return ['success' => true, 'name' => $dataSet['login']];
        } else {
            $result->close();
            return ['success' => false];
        }

    }
    /**
    * @param $sql
    * @param $params
    */
    public static function getDBDatas (string $sql, array $params = [])
    : self
    {

        $stmt = self::_prepareRequest($sql, $params);
        /* Execute statement */
        self::_executeQuery($stmt);
        self::$_result = $stmt->get_result();
        return self::$_instance;
    }

    public static function toArray()
    : array
    {
        $resultSet = MYSQLI_NUM;
        return self::_getResult($resultSet);
    }

    public static function toArrayAssoc()
    : array
    {
        $resultSet = MYSQLI_ASSOC;
        return self::_getResult($resultSet);

    }

    public static function toObject()
    : array
    {

        if((self::$_user || self::getCurrentUser()['success']) && self::$_result->num_rows) {
            $dataSet = self::$_result->fetch_object();

            self::$_result->close();
            return ['success' => true, 'result' => $dataSet, 'session' => SessionManager::getSession()];
        } else {
            self::$_result->close();
            return ['success' => false];
        }
    }

    private static function _getResult(int $resultSet)
    : array
    {
        if((self::$_user || self::getCurrentUser()['success']) && self::$_result->num_rows) {
            $dataSet = self::$_result->fetch_all($resultSet);
            self::$_result->close();
            return ['success' => true, 'result' => $dataSet, 'session' => SessionManager::getSession()];
        } else {
            self::$_result->close();
            return ['success' => false];
        }
    }

    /**
    * @param $table
    * @param $sql
    * @param $params
    */
    public static function setDBDatas(string $table, string $sql, array $params = [])
    : int
    {
        if(self::$_user || self::getCurrentUser()['success']) {
            $stmt = self::_prepareRequest("INSERT INTO ".$table." ".$sql, $params);
            return self::_executeQuery($stmt) ? self::$_mysqli->insert_id : 0;
            //return last ID
        }
        return 0;
    }

    /**
    * @param $table
    * @param $sql
    * @param $params
    */
    public static function unsetDBDatas(string $table, string $sql, array $params = [])
    : bool
    {
        if(self::$_user || self::getCurrentUser()['success']) {
            $stmt = self::_prepareRequest("DELETE FROM ".$table." WHERE ".$sql, $params);
            return self::_executeQuery($stmt);
        }
        return false;
    }

    /**
    * @param $$table
    * @param $sql
    * @param $params
    */
    public static function updateDBDatas(string $table, string $sql, array $params = [])
    : bool
    {
        if(self::$_user || self::getCurrentUser()['success']) {
            $stmt = self::_prepareRequest("UPDATE ".$table." SET ".$sql, $params);
            return self::_executeQuery($stmt);
        }
        return false;
    }


    private static function _prepareRequest (string $sql, array $a_bind_params)
    : \mysqli_stmt
    {

        if(($stmt = self::$_mysqli->prepare($sql)) === false) {
            Log::error(
                "Wrong SQL: {query} error. {errno} {error}",
                ['query' => $sql, 'errno' => self::$_mysqli->errno, 'error' => self::$_mysqli->error]
            );
        }

        if(!empty($a_bind_params)) {
            $type_st = ["integer" => 'i', "string" => 's', "double" => 'd', "blob" => 'b'];
            $type = [];
            foreach($a_bind_params as &$param) {
                $param = htmlspecialchars($param); //XSS securisation
                $type[] = $type_st[gettype($param)];
            }
            unset($param);
            $a_params = array();
            $param_type = '';
            $n = count($type);
            for($i = 0; $i < $n; $i++) {
                $param_type .= $type[$i];
            }
            $a_params[] = &$param_type;
            for($i = 0; $i < $n; $i++) {
                $a_params[] =& $a_bind_params[$i];
            }
            call_user_func_array(array($stmt, 'bind_param'), $a_params);
        }

        return $stmt;
    }

    private static function _executeQuery (\mysqli_stmt &$stmt)
    : bool
    {
        if(!$stmt->execute()) {
            Log::error(
                "Error to execute SQL query {error}", ['error' => $stmt->error]
            );
            return false;
        }
        return true;
    }

}
