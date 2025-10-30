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
            $color = $msg['success'] ? 'bg-emerald-300' : 'bg-red-300';
            return "<p id='message' class='fixed text-center top-10 left-1/2 -translate-x-1/2 p-2 w-[300px] $color' text-white font-serif>{$msg['message']}</p>";
        }

        return null;
    }


}
