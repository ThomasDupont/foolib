<?php

namespace src;

/***********************************************************************************************
 * Angular->php standard web service  - Full native php web service Angular friendly
 *   ControllerFactory.php Factory of controller for the MVC Pattern
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

use src\http\Http;
use src\models\mysql\SessionManager;
use src\exceptions\HttpException;

/**
 * Class ControllerFactory
 * @package src
 */
final class ControllerFactory
{
    /**
    * @param Object Http()
    */
    public static function load(Http $http): string
    {
        $type = $http->getHttp()->controller;
        if ($type === 'csrf') {
            return self::CSRFToken();
        }

        if (!self::checkCSRF($http)) {
            return json_encode(['success' => false, 'message' => "CSRF token not valid"]);
        }

        $class = 'src\controllers\\' . $type . 'Controller';

        if (!class_exists($class)) {
            throw new HttpException('bad_route', 400);
        }

        return (new $class($http))->execute();
    }

    /**
     * @param string $test
     * @return string
     */
    private static function CSRFToken(string $test = "false"): string
    {
        if ($test != "false") {
            return SessionManager::getCSRFToken() == $test;
        }
        $token = hash('sha512', uniqid()."NMCAECTMD");
        SessionManager::setCSRFToken($token);
        return $token;
    }

    /**
     * @param Http $http
     * @return bool
     */
    private static function checkCSRF(Http $http): bool
    {
        $csrf = $http->getHttp()->csrf ?? false;
        return !(CSRFENABLE  && ((!isset($csrf) || $csrf == "" || !$csrf) || !self::CSRFToken($csrf))) ;
    }
}
