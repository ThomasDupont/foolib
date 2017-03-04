<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   config.php configuration file
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin;

/**
* @package Controller
*/
define("CSRFENABLE"         , true);

/**
* @package Mysql
*/
define("SQLIP"              ,"127.0.0.1");
define("SQLPORT"            ,8889);
define("DATABASE"           ,"Angular");
define("SQLUSER"            ,"root");
define("SQLPWD"             ,"root");
define("OFFUSC"             , "base64_decode");

/**
* @package Mongo
*/
define("MONGOIP"              ,"127.0.0.1");
define("MONGOPORT"            ,27017);
define("MONGODATABASE"           ,"save");
define("MONGOUSER"            ,"root");
define("MONGOPWD"             ,"root");

/**
* @package Autoloader
*/
define("DS"                 , DIRECTORY_SEPARATOR);
define("ROOT"               , dirname(__FILE__).DS."..".DS);

/**
* @package Upload
*/
define("MAX_FILE_SIZE"      , 10000000);
define("FILE_TYPES"         , 'jpeg,jpg,png');
define("MAX_FILE_NUMBER"    , 3);
define("USERDIR"            , ROOTDIR."PRODUCTION/" );
define("FILETMPDIR"         , ROOTDIR."tmp/upload/" );
define("LOGTMPDIR"          , ROOTDIR."tmp/logs/" );

/**
* @package Log
*/
define("DEBUG", true);
define("LOG_ERROR_FILE"     , ROOT."tmp/logs/error.log");
define("LOG_WARNING_FILE"   , ROOT."tmp/logs/warning.log");
define("LOG_DEBUG_FILE"     , ROOT."tmp/logs/debug.log");

/**
* @package Imagick
*/
define("WIDTH", 140);
define("HEIGHT", 140);
