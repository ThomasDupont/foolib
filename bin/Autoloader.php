<?php

namespace bin;
/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   Autoloader.php Class of autoloading
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

/**
* @PSR PSR-0, PSR-1, PSR-2, PSR-3 , PSR-4 (partial)
*/


final class Autoloader {

    public static function register()
    : void
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    private static function autoload(string $class)
    : void
    {
        $parts = preg_split("#\\\#", $class);
        $className = array_pop($parts);
        require_once ROOT.strtolower(implode(DS, $parts)).DS.$className.'.php';
    }
}
