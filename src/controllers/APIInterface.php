<?php

/***********************************************************************************************
 * Angular->php standard web service - Full native php web service Angular friendly
 *   APIInterface.php Interface for controller management
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

namespace src\controllers;

/**
* Method to be use in each controllers
*
*/
interface APIInterface
{
    public function execute(): string;
}
