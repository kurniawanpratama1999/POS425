<?php
namespace App\Utils;

class Response
{
    public static function success($res)
    {
        return ["success" => true, "message" => $res];
    }
    public static function failure($res)
    {
        return ["success" => false, "message" => $res];
    }
}