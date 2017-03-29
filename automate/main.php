<?php

declare(strict_types = 1);
date_default_timezone_set('Etc/UTC');
define("ROOTDIR", __DIR__."/");
require_once("Autoloader.php");
require_once("config.php");
require_once(ROOTDIR.'../vendor/autoload.php');
Autoloader::register();

$email = new classes\Emailing();
$email->execute([]);
