<?php
namespace App\Controllers;

use App\Models\{UsersModel};
use App\Utils\Message;

class UsersControl
{
    public function create()
    {
        session_start();
        $name = $_POST['name'] ?? null;
        $role_id = $_POST['role_id'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($name && $password && $email) {
            $sendSessionToModels = [
                "name" => $name,
                "role_id" => $role_id,
                "email" => $email,
                "password" => $password,
            ];
            $result = UsersModel::create($sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }
        } else {
            Message::failure("Semua field wajib diisi!");
        }

        header("Location:/dashboard/users");
        exit;
    }

    public function update($paramUsersID)
    {
        session_start();
        $name = $_POST['name'] ?? null;
        $role_id = $_POST['role_id'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($name && $role_id && $email) {
            $sendSessionToModels = [
                "name" => $name,
                "role_id" => $role_id,
                "email" => $email,
                "password" => $password,
            ];
            ;
            $result = UsersModel::update($paramUsersID, $sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }

        } else {
            Message::failure('Terjadi Kesalahan');
        }

        header("Location:/dashboard/users");
        exit;
    }

    public function softDelete($paramUsersID)
    {
        session_start();

        $result = UsersModel::softDelete($paramUsersID, );

        if ($result['success']) {
            Message::success($result['message']);
        } else {
            Message::failure($result['message']);
        }

        header("Location:/dashboard/users");
        exit;
    }
}