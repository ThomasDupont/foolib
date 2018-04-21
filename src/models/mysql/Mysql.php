<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   Mysql.php Mysql interface full documented used as service
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace src\models\mysql;

use src\log\Log;

/**
 * Class Mysql
 * @package src\models\mysql
 */
class Mysql
{

    /**
    * @var Object Mysqli connect
    *
    */
    private static $mysqli;

    /**
    * @var Object Mysql()
    *
    */
    private static $instance;

    /**
    * @var Object Query result
    *
    */
    private static $result;

    public static $error;

    /**
    * @var Boolean
    * Used for connection and registration, false by default
    */
    public static $user = false;

    /**
     * Set to true to use query with out user control
     * @param bool $bool
     */
    public static function setUser(bool $bool): void
    {
        self::$user = $bool;
    }

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        self::$mysqli = mysqli_init();
        try {
            if (!self::$mysqli) {
                throw new \HttpException(Log::error("mysqli_init failed"), 503);
            }
            if (!self::$mysqli->real_connect(SQLIP, SQLUSER, SQLPWD, DATABASE, SQLPORT)) {
                throw new \HttpException(Log::error("Connect Error ({errno}) {error}", ['errno' => mysqli_connect_errno(), 'error' => mysqli_connect_error()]), 503);
            }
        } catch (\Exception $e) {
            throw new \HttpException(json_encode(['success' => false, 'message' => $e->getMessage()]), 400);
        }
    }

    public function __destruct()
    {
        self::$mysqli->close();
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getCurrentUser(int $id = 0): array
    {
        if (!$id) {
            $sql = "SELECT login, email, pp FROM users WHERE API_key = '".SessionManager::getSession()['APITOKEN']."'";
            $result = self::$mysqli->query($sql);
            if ($result->num_rows) {
                $dataSet = $result->fetch_array();
                $result->close();

                return ['success' => true, 'name' => $dataSet['login'], 'email' => $dataSet['email'], 'pp' => $dataSet['pp']];
            } else {
                $result->close();

                return ['success' => false];
            }
        }
        $sql = "SELECT API_key, pp, email, login FROM users WHERE id = ?";
        $stmt = self::$mysqli->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->bind_result($apikey, $pp, $email, $login);
        $stmt->fetch();
        $stmt->close();

        return ['success' => true, 'apikey' => $apikey, 'pp' => $pp, 'email' => $email, 'login' => $login];
    }

    /**
     * @param string $sql
     * @param array $params
     * @return Mysql
     */
    public static function getDBDatas(string $sql, array $params = []): self
    {
        $stmt = self::prepareRequest($sql, $params);
        /* Execute statement */
        self::executeQuery($stmt);
        self::$result = $stmt->get_result();
        return self::$instance;
    }

    /**
     * @return array
     */
    public static function toArray(): array
    {
        return self::getResult(MYSQLI_NUM);
    }

    /**
     * @return array
     */
    public static function toArrayAssoc(): array
    {
        return self::getResult(MYSQLI_ASSOC);
    }

    /**
     * @return array
     */
    public static function toObject(): array
    {
        if ((self::$user || self::getCurrentUser()['success']) && self::$result->num_rows) {
            $dataSet = self::$result->fetch_object();

            self::$result->close();
            return ['success' => true, 'result' => $dataSet, 'session' => SessionManager::getSession()];
        }
        self::$result->close();

        return ['success' => false, 'result' => ""];
    }

    /**
     * @param int $resultSet
     * @return array
     */
    private static function getResult(int $resultSet): array
    {
        if ((self::$user || self::getCurrentUser()['success']) && self::$result->num_rows) {
            $dataSet = self::$result->fetch_all($resultSet);
            self::$result->close();

            return ['success' => true, 'result' => $dataSet, 'session' => SessionManager::getSession()];
        }
        self::$result->close();

        return ['success' => false];
    }

    /**
     * @param string $table
     * @param string $sql
     * @param array  $params
     * @return int
     */
    public static function setDBDatas(string $table, string $sql, array $params = []): int
    {
        if (self::$user || self::getCurrentUser()['success']) {
            $stmt = self::prepareRequest("INSERT INTO ".$table." ".$sql, $params);
            return self::executeQuery($stmt) ? self::$mysqli->insert_id : 0;
            //return last ID
        }
        return 0;
    }

    /**
     * @param string $table
     * @param string $sql
     * @param array  $params
     * @return bool
     */
    public static function unsetDBDatas(string $table, string $sql, array $params = []): bool
    {
        if (self::$user || self::getCurrentUser()['success']) {
            $stmt = self::prepareRequest("DELETE FROM ".$table." WHERE ".$sql, $params);

            return self::executeQuery($stmt);
        }

        return false;
    }

    /**
    * @param $$table
    * @param $sql
    * @param $params
    */
    public static function updateDBDatas(string $table, string $sql, array $params = []): bool
    {
        if (self::$user || self::getCurrentUser()['success']) {
            $stmt = self::prepareRequest("UPDATE ".$table." SET ".$sql, $params);

            return self::executeQuery($stmt);
        }

        return false;
    }

    /**
     * @param string $sql
     * @param array  $aBindParams
     * @return \mysqli_stmt
     */
    private static function prepareRequest(string $sql, array $aBindParams): \mysqli_stmt
    {
        $stmt = self::$mysqli->prepare($sql);

        if (count($aBindParams)) {
            $typeSt = ["integer" => 'i', "string" => 's', "double" => 'd', "blob" => 'b'];
            $type = "";
            foreach ($aBindParams as &$param) {
                $param = htmlspecialchars($param); //XSS securisation
                $type .= $typeSt[gettype($param)];
            }
            $stmt->bind_param($type, ...$aBindParams);
        }

        return $stmt;
    }

    /**
     * @param \mysqli_stmt $stmt
     * @return bool
     */
    private static function executeQuery(\mysqli_stmt &$stmt): bool
    {
        if (!$stmt->execute()) {
            self::$error = $stmt->error;
            Log::error(
                "Error to execute SQL query {error}",
                ['error' => $stmt->error]
            );
            return false;
        }

        return true;
    }
}
