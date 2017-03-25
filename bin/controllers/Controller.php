<?php

namespace bin\controllers;

use bin\http\Http;
use PHPMailer\PHPMailer\PHPMailer;

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

    /**
    * Intance of phpmailer
    */
    protected $phpmailer;

    public function __construct()
    {
        $this->_instanceDir();
        $this->_instancePhpMailer();

        $this->request = Http::getHttp();
        $this->server = Http::getServer();
    }

    private function _instancePhpMailer()
    : void
    {
        $this->phpmailer = new PHPMailer();
        $this->phpmailer->isSMTP();
        //$this->phpmailer->SMTPDebug = 2;
        //$this->phpmailer->Debugoutput = 'html';
        $this->phpmailer->Host = SMTPHOST;
        $this->phpmailer->SMTPAuth = SMTPAUTH;
        $this->phpmailer->Port = SMTPPORT;
        $this->phpmailer->SMTPSecure = SMTPSECURE;
        $this->phpmailer->Username = SMTPUSERNAME;
        $this->phpmailer->Password = SMTPPASSWORD;
    }

    private function _instanceDir()
    : void
    {
        $oldmask = umask(0);
        if(!is_dir(FILETMPDIR)) {
            mkdir(FILETMPDIR, 0777, true);
        }
        if(!is_dir(USERDIR)) {
            mkdir(USERDIR, 0777, true);
        }
        if(!is_dir(LOGTMPDIR)) {
            mkdir(LOGTMPDIR, 0777, true);
        }
        umask($oldmask);
    }
}
