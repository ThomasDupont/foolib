<?php

namespace src;

/***********************************************************************************************
 * Angular->php standard web service  - Full native php web service Angular friendly
 *   ControllerFactory.php Factory of controller for the MVC Pattern
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

use src\http\Http;
use src\controllers\AjaxController;
use src\controllers\CodeController;
use src\log\Log;
use src\models\mysql\SessionManager;

/**
* @pattern Factory
* All controller call must be secure with the CSRF token validation
*/
final class ControllerFactory
{
    /**
    * @param Object Http()
    */
    public static function load(Http $http)
    : string
    {
        $type = $http->getHttp()->controller ?? "";
        if ($type === 'csrf') {
            return self::_CSRFToken();
        }

        if (!self::_checkCSRF($http)) {
            return json_encode(['success' => false, 'message' => "CSRF token not valid"]);
        }

        $class = "src\controllers\\".ucfirst($type) . 'Controller';
        try {
            $exec = (new $class($http))->execute();
        } catch (\Exception $e) {
            throw new \HttpException('bad_route', 400);
        }

        return $exec;
    }

    private static function _CSRFToken(string $test = "false")
    : string
    {
        if ($test != "false") {
            return SessionManager::getCSRFToken() == $test;
        }
        $token = hash('sha512', uniqid()."NMCAECTMD");
        SessionManager::setCSRFToken($token);
        return $token;
    }

    private static function _checkCSRF(Http $http)
    : bool
    {
        $csrf = $http->getHttp()->csrf ?? false;
        return !(CSRFENABLE  && ((!isset($csrf) || $csrf == "" || !$csrf) || !self::_CSRFToken($csrf))) ;
    }
}
