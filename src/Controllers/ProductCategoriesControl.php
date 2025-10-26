<?php
namespace App\Controllers;

use App\Models\ProductCategoriesModel;
use App\Utils\Message;

class ProductCategoriesControl
{
    public function create()
    {
        session_start();
        $name = $_POST['name'] ?? null;

        if ($name) {
            $sendSessionToModels = ["name" => $name];
            $result = ProductCategoriesModel::create($sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }
        } else {
            Message::failure("Semua field wajib diisi!");
        }

        header("Location:/dashboard/product-categories");
        exit;
    }

    public function update($id)
    {
        session_start();
        $name = $_POST['name'] ?? null;

        if ($name) {
            $sendSessionToModels = ["name" => $name];
            $result = ProductCategoriesModel::update($id, $sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }

        } else {
            Message::failure('Terjadi Kesalahan');
        }

        header("Location:/dashboard/product-categories");
        exit;
    }

    public function softDelete($id)
    {
        session_start();
        $result = ProductCategoriesModel::softDelete($id, );

        if ($result['success']) {
            Message::success($result['message']);
        } else {
            Message::failure($result['message']);
        }


        header("Location:/dashboard/product-categories");
        exit;
    }
}