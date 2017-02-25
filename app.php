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
session_start();

require_once("bin/config.php");
require_once("bin/Autoloader.php");

bin\Autoloader::register();
if(($post = json_decode(file_get_contents("php://input"))) === null) {
    exit("Merci de passer un objet JSON");
}
try {
    $http = bin\http\Http::getInstance()->setHttp($post)->parseURI($_SERVER['REQUEST_URI']);
    $response = bin\ControllerFactory::load($http);
} catch(\Error $e) {
    $response = json_encode(['success' => false, 'error' => "PHP Engine Exception", 'message' => DEBUG ? $e->getMessage() : "An error occured"]);
} catch(\Exception $e) {
    $response = json_encode(['success' => false, 'error' => "PHP Exception", 'message' => DEBUG ? $e->getMessage() : "An error occured"]);
}
echo <<<JSON
{$response}
JSON;
