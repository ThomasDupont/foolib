<?php

namespace src\services;

use \PHPMailer\PHPMailer\PHPMailer;
use src\models\mysql\Mysql;
use src\log\Log;
use src\exceptions\HttpException;

final class Emailing
{
    private static $token;
    public static function sendEmail(string $email, string $login, int $action): bool
    {
        static::$token = hash('sha512', uniqid().$email."NMCAECTMD");
        switch ($action) {
            case 1:
                $body = static::getRegisterTemplate();
                $subject = "Confirmation of your email";
                $link = DOMAIN."#/link?type=confirm&token=".static::$token;
                $body = str_replace(['{name}','{link}'] , [$login, $link], $body);
                break;
            case 2:
                $body = static::getPwdForgetTemplate();
                $subject = "Generation of a new password";
                $link = DOMAIN."#/link?type=forget&token=".static::$token;
                $body = str_replace(['{link}'] , [$link], $body);
                break;
            default:
                throw new HttpException("not reconize action $action, emailing service", 400);
                break;
        }

        $phpMailer = new PHPMailer();
        $phpMailer->isSMTP();
        $phpMailer->Host = SMTPHOST;
        $phpMailer->SMTPAuth = SMTPAUTH;
        $phpMailer->Port = SMTPPORT;
        $phpMailer->SMTPSecure = SMTPSECURE;
        $phpMailer->Username = SMTPUSERNAME;
        $phpMailer->Password = SMTPPASSWORD;
        $phpMailer->SetFrom(FOOLIBADRESS, 'contact foolib');
        $phpMailer->AddAddress($email, $login);
        $phpMailer->Subject = $subject;
        $phpMailer->MsgHTML($body);

        if(!$phpMailer->Send()) {
            Log::user("Erreur envois email confirm, mail: {mail}, error: {error}", ['mail' => $email, 'error' => $phpMailer->ErrorInfo]);
            return false;
        }
        return true;
    }

    /**
     * @param string $column
     * @param string $email
     * @return bool
     */
    public static function updateDb(string $column, string $email): bool
    {
        return Mysql::getInstance()->updateDBDatas('users', "$column = ? WHERE email = ?", [static::$token, $email]);
    }

    private static function getRegisterTemplate(): string
    {
        return file_get_contents(ROOT."var/emailRegister.html");
    }

    private static function getPwdForgetTemplate(): string
    {
        return file_get_contents(ROOT."var/emailForgot.html");;
    }
}
