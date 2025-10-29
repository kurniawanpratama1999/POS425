<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\LoginModel;
use App\Utils\Message;

class LoginControl
{

    public function login()
    {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        $sendData = [
            "email" => $email,
            "password" => $password,
        ];

        if ($email && $password) {
            $result = LoginModel::post($sendData);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }
        } else {
            Message::failure("Gagal login, silahkan coba lagi");
        }

        header("Location:/dashboard/users");
        exit;
    }

    public function delete()
    {
        session_destroy();

        header("Location:/");
        exit;
    }
}
