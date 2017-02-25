<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   LoggerInterface.php PSR-3 full complaiance
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace bin\log;

interface LoggerInterface
{
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param $message
     * @param $context
     */
    public static function error(string $message, array $context = [])
    : self;

    /**
     * Detailed debug information.
     *
     * @param $message
     * @param $context
     */
    public static function debug(string $message, array $context = [])
    : self;

    /**
     * Not blocking error.
     *
     * @param $message
     * @param $context
     */
    public static function warning(string $message, array $context = [])
    : self;
}
