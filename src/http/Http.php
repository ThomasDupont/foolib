<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   Http.php Treatement of http request as object
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace src\http;

final class Http
{

    /**
    * The Post or Get request
    * @var object $request
    */
    private static $request;

    /**
    * All HTTP header concerning server
    * @var object $server
    */
    public static $server;

    public static $router;

    /**
    * @var object $instance
    */
    private static $instance;

    public static function getInstance()
    : self
    {
        if (is_null(static::$instance)) {
            static::$instance = new self;
        }
        return static::$instance;
    }

    private function __construct()
    {
        static::server();
    }

    private static function server(): void
    {
        static::$server = (object) $_SERVER;
        static::getRouter();
    }


    /**
    * set http request (POST and GET)
    * @param object $request
    */
    public static function setHttp(\stdClass $request): self
    {
        static::$request = $request;
        return static::$instance;
    }

    /**
    * parse the rest parameters
    * @param string $uri
    */
    public static function parseURI(): self
    {
        $path = static::$server->PATH_INFO;
        $method = static::$server->REQUEST_METHOD;

        $call = (object) static::$router[$path] ?? false;

        if (!$call) {
            throw new \HttpException("$path not found", 404);
        }

        if ($method !== $call->verb) {
            throw new \HttpException("cannot $method for $path", 400);
        }

        static::$request->controller = $call->controller;
        static::$request->action = $call->method;


        /*
        $parse = explode("/", $uri);
        $lenght = count($parse);
        if ($lenght-3 < 0) {
            static::$request->controller = static::$request->action = "";
        } else {
            static::$request->controller = $parse[$lenght-3];
            static::$request->action = $parse[$lenght-2];
        }
        */
        return static::$instance;
    }

    /**
    * get http request (POST and GET)
    */
    public static function getHttp(): \stdClass
    {
        return static::$request;
    }

    /**
    * set http server variable
    */
    public static function getServer(): \stdClass
    {
        return static::$server;
    }

    private static function getRouter(): void
    {
        static::$router = (array) json_decode(file_get_contents(__DIR__ . '/../config/routing.json'));
    }
}
