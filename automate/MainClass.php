<?php

use PHPMailer\PHPMailer\PHPMailer;

abstract class MainClass {

    protected $phpmailer;

    public function __construct()
    {
        //Log::user("Erreur envois email confirm, mail: {mail}", ['mail' => "ici"]);
        $this->_instancePhpMailer();
        $this->_instanceDir();
    }

    abstract protected function execute(array $args) : void;

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
        if(!is_dir(LOGTMPDIR)) {
            mkdir(LOGTMPDIR, 0777, true);
        }
        umask($oldmask);
    }
}
