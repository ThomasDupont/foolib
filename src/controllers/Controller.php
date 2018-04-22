<?php

namespace src\controllers;

use src\http\Http;
use src\exceptions\HttpException;

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
    }

    /**
     * @return string
     * @throws src\exceptions\HttpException
     */
    public function execute(): string
    {
        $funct = $this->request->action;

        if ($funct === __FUNCTION__ || !method_exists($this, $funct)) {
            throw new HttpException('wrong_action', 400);
        }

        return $this->$funct();
    }

    /**
     *
     */
    private function instanceDir(): void
    {
        if (FILESYSTEM) {
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
}
