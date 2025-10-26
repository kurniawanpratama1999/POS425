<?php
namespace App\Controllers;

use App\Models\{ProductsModel};
use App\Utils\Message;

class ProductsControl
{
    public function create()
    {
        session_start();
        $name = $_POST['name'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $description = $_POST['description'] ?? null;
        $price = $_POST['price'] ?? null;
        $photo = $_FILES['photo']['tmp_name'] ? $_FILES['photo'] : null;

        if ($name && $category_id && $description && $price && $photo) {
            $sendSessionToModels = [
                "name" => $name,
                "category_id" => $category_id,
                "description" => $description,
                "price" => $price,
                "photo" => $photo,
            ];
            $result = ProductsModel::create($sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }
        } else {
            Message::failure("Semua field wajib diisi!");
        }

        header("Location:/dashboard/products");
        exit;
    }

    public function update($paramProductsID)
    {
        session_start();
        $name = $_POST['name'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $description = $_POST['description'] ?? null;
        $price = $_POST['price'] ?? null;
        $photo = $_FILES['photo']['tmp_name'] ? $_FILES['photo'] : null;

        if ($name && $category_id && $description && $price) {
            $sendSessionToModels = [
                "name" => $name,
                "category_id" => $category_id,
                "description" => $description,
                "price" => $price,
                "photo" => $photo,
            ];
            ;
            $result = ProductsModel::update($paramProductsID, $sendSessionToModels);

            if ($result['success']) {
                Message::success($result['message']);
            } else {
                Message::failure($result['message']);
            }

        } else {
            Message::failure('Terjadi Kesalahan');
        }

        header("Location:/dashboard/products");
        exit;
    }

    public function softDelete($paramProductsID)
    {
        session_start();

        $result = ProductsModel::softDelete($paramProductsID, );

        if ($result['success']) {
            Message::success($result['message']);
        } else {
            Message::failure($result['message']);
        }

        header("Location:/dashboard/products");
        exit;
    }
}