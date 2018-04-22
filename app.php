<?php

/***********************************************************************************************
 * Angular->php 7.1 standard REST API  - Full native php web service Angular friendly
 * BE CAREFULL, only for php 7.1 or highter
 *   app.php destination of all API request
 *   Version: 0.1.2
 * Copyright 2016-2017 Thomas DUPONT
 * MIT License
 ************************************************************************************************/
declare(strict_types = 1);

define("ROOTDIR", __DIR__."/");
date_default_timezone_set('Etc/UTC');
session_start();

require_once __DIR__.'/vendor/autoload.php';
require_once("src/config/config.php");
require_once("src/Autoloader.php");

src\Autoloader::register();

if(($post = json_decode(file_get_contents("php://input"))) === null) {
    throw new src\exceptions\HttpException('Thanks to pass a json object', 400);
}

$response = src\ControllerFactory::load(src\http\Http::getInstance()->setHttp($post)->parseURI());
echo <<<JSON
{$response}
JSON;
