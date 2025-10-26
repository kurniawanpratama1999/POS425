<?php
namespace App\Controllers;

use App\Models\UserRolesModel;
use App\Utils\Message;

class UserRolesControl
{
    public function create()
    {
        session_start();
        $name = $_POST['name'] ?? null;

        if ($name) {
            $sendSessionToModels = ["name" => $name];
            $result = UserRolesModel::create($sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }
        } else {
            Message::failure("Semua field wajib diisi!");
        }

        header("Location:/dashboard/user-roles");
        exit;
    }

    public function update($id)
    {
        session_start();
        $name = $_POST['name'] ?? null;

        if ($name) {
            $sendSessionToModels = ["name" => $name];
            $result = UserRolesModel::update($id, $sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }

        } else {
            Message::failure('Terjadi Kesalahan');
        }

        header("Location:/dashboard/user-roles");
        exit;
    }

    public function softDelete($id)
    {
        session_start();
        $result = UserRolesModel::softDelete($id, );

        if ($result['success']) {
            Message::success($result['message']);
        } else {
            Message::failure($result['message']);
        }


        header("Location:/dashboard/user-roles");
        exit;
    }
}