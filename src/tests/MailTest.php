<?php

use PHPUnit\Framework\TestCase;
use src\services\Emailing;

define("ROOTDIR", __DIR__ . "/");
require_once __DIR__.'/../Autoloader.php';
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

src\Autoloader::register();

class MailTest extends TestCase
{
    public function testSendEmail() {
        $email = Emailing::sendEmail('dupont.thomas70@gmail.com', 'thomas', 1);
        $this->assertEquals($email, true);
    }
}