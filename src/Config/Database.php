<?php

namespace App\Config;

class Database
{
    public static function connect()
    {
        $HOST = 'localhost';
        $USER = "root";
        $PASS = "";
        $DB = "testing";

        $conn = mysqli_connect($HOST, $USER, $PASS, $DB);

        if (!$conn) {
            die("Koneksi ke $DB gagal! -> " . mysqli_connect_errno());
        }

        return $conn;
    }
}
