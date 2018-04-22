<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   config.php configuration file
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace src\config;

use \Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

/**
 * @package Controller
 */
define("CSRFENABLE", true);

/**
 * @package Mysql
 */
define("SQLIP", getenv('SQLIP'));
define("SQLPORT", (int) getenv('SQLPORT'));
define("DATABASE", getenv('DATABASE'));
define("SQLUSER", getenv('SQLUSER'));
define("SQLPWD", getenv('SQLPWD'));

/**
 * @package Mongo
 */
define("MONGOHOST", getenv('MONGOHOST'));
define("MONGOPORT", (int) getenv('MONGOPORT'));
define("MONGODATABASE", getenv('MONGODATABASE'));
define("MONGOUSER", getenv('MONGOUSER'));
define("MONGOPWD", getenv('MONGOPWD'));

/**
 * @package Autoloader
 */
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(__FILE__).DS."..".DS."..".DS);

/**
 * @package Upload
 */
define("MAX_FILE_SIZE", 10000000);
define("FILE_TYPES", 'jpeg,jpg,png');
define("MAX_FILE_NUMBER", 3);
define("USERDIR", ROOTDIR."PRODUCTION/");
define("FILETMPDIR", ROOTDIR."tmp/upload/");
define("LOGTMPDIR", ROOTDIR."tmp/logs/");

/**
 * @package Log
 */
define("DEBUG", true);
define("LOG_ERROR_FILE", ROOT."tmp/logs/error.log");
define("LOG_WARNING_FILE", ROOT."tmp/logs/warning.log");
define("LOG_DEBUG_FILE", ROOT."tmp/logs/debug.log");
define("LOG_USER_FILE", ROOT."tmp/logs/user.log");

/**
 * @package Imagick
 */
define("WIDTH", 110);
define("HEIGHT", 110);

/**
 * @package email
 */
define('SMTPHOST', getenv('SMTPHOST'));
define('SMTPPORT', (int) getenv('SMTPPORT'));
define('SMTPAUTH', (bool) getenv('SMTPAUTH'));
define('DOMAIN', getenv('DOMAIN'));
define('SMTPUSERNAME', getenv('SMTPUSERNAME'));
define('SMTPPASSWORD', getenv('SMTPPASSWORD'));
define('SMTPSECURE', getenv('SMTPSECURE'));
define('FOOLIBADRESS', getenv('FOOLIBADRESS'));