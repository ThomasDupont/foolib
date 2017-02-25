<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   Log.php log management, used as service
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\log;

final class Log implements LoggerInterface{

    /**
    * @var Object Log()
    *
    */
    private static $_instance;

    /**
    * @var string $message
    *
    */
    private static $_message;

    private function __construct()
	{
	}

    private static function _getInstance ()
    : self
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
    * @param $message
    * @param $context
    */
    public static function error (string $message, array $context = [])
    : LoggerInterface
    {
        self::_interpolate($message, $context);
        $error = var_export($message, true);
        $date = date('l jS \of F Y h:i:s A');
        $date = var_export($date, true);

        file_put_contents(LOG_ERROR_FILE, "Error Trigger at $date: ".$error."\n", FILE_APPEND);
        return self::_getInstance();
    }

    /**
    * @param $message
    * @param $context
    */
    public static function debug (string $message, array $context = [])
    : LoggerInterface
    {
        if(DEBUG) {
            self::_interpolate($message, $context);
            return self::_getInstance();
        }
    }

    /**
    * @param $message
    * @param $context
    */
    public static function warning (string $message, array $context = [])
    : LoggerInterface
    {
        self::_interpolate($message, $context);
        return self::_getInstance();
    }

    public function __toString()
    {
        return self::$_message;
    }

    private static function _interpolate (string &$message, array $context)
    : void
    {
        $replace = [];
        foreach ($context as $index => $type) {
            if (!is_array($type) && (!is_object($type) || method_exists($type, '__toString'))) {
                $replace['{' . $index . '}'] = $type;
            }
        }
        $message = strtr($message, $replace);
        self::$_message = $message;
    }
}
