<?php
namespace App\Utils;

class Message
{
    public static function success($msg)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['_MESSAGE_'] = ["success" => true, "message" => $msg];
    }

    public static function failure($msg)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['_MESSAGE_'] = ["success" => false, "message" => $msg];
    }

    public static function get()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['_MESSAGE_'])) {
            $msg = $_SESSION['_MESSAGE_'];
            unset($_SESSION['_MESSAGE_']);
            return $msg;
        }

        return null;
    }


}
