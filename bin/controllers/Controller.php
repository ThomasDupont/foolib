<?php

namespace bin\controllers;

use bin\http\Http;

abstract class Controller {

    /**
    * @var Object Http request
    *
    */
    protected $request;

    /**
    * All HTTP header concerning server
    * @var object $server
    */
    protected $server;

    public function __construct()
    {
        $this->_instanceDir();
        $this->request = Http::getHttp();
        $this->server = Http::getServer();
    }

    private function _instanceDir()
    : void
    {
        $oldmask = umask(0);
        if(!is_dir(FILETMPDIR)) {
            mkdir(FILETMPDIR, 0777);
        }
        if(!is_dir(USERDIR)) {
            mkdir(USERDIR, 0777);
        }
        umask($oldmask);
    }
}
