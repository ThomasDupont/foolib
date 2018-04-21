<?php

namespace src\controllers;

use src\http\Http;

abstract class Controller
{

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
        $this->instanceDir();

        $this->request = Http::getHttp();
        $this->server = Http::getServer();

        if ($this->request->action === 'execute') {
            throw new \HttpException('wrong_action', 400);
        }
    }

    private function instanceDir()
    : void
    {
        $oldmask = umask(0);
        if (!is_dir(FILETMPDIR)) {
            mkdir(FILETMPDIR, 0777, true);
        }
        if (!is_dir(USERDIR)) {
            mkdir(USERDIR, 0777, true);
        }
        if (!is_dir(LOGTMPDIR)) {
            mkdir(LOGTMPDIR, 0777, true);
        }
        umask($oldmask);
    }
}
