<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   Http.php Treatement of http request as object
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\http;

final class Http {

    /**
    * The Post or Get request
    * @var object $request
    */
    private static $_request;

    /**
    * All HTTP header concerning server
    * @var object $server
    */
    public static $_server;

    /**
    * @var object $instance
    */
    private static $_instance;

    public static function getInstance()
    : self
    {
        if(is_null(static::$_instance)) {
            static::$_instance = new self;
        }
        return static::$_instance;
    }

    private function __construct()
    {
        static::_server();
    }

    private static function _server()
    : void
    {
        static::$_server = (object) $_SERVER;
    }


    /**
    * set http request (POST and GET)
    * @param object $request
    */
    public static function setHttp(\stdClass $request)
    : self
    {
        static::$_request = $request;
        return static::$_instance;
    }

    /**
    * parse the rest parameters
    * @param string $uri
    */
    public static function parseURI(string $uri)
    : self
    {
        $parse = explode("/", $uri);
        $lenght = count($parse);
        static::$_request->controller = $parse[$lenght-3];
        static::$_request->action = $parse[$lenght-2];
        return static::$_instance;
    }

    /**
    * get http request (POST and GET)
    */
    public static function getHttp()
    : \stdClass
    {
        return static::$_request;
    }

    /**
    * set http server variable
    */
    public static function getServer()
    : \stdClass
    {
        return static::$_server;
    }

}
