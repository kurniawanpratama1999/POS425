<?php
namespace App\Models;

use App\Config\Database;
use App\Utils\Response;

class ProductsModel
{
    public static function uploadPhoto($photo)
    {
        if (!$photo) {
            return ["success" => false, 'message' => 'Kamu belum upload photo!'];
        }

        if ($photo['error'] !== UPLOAD_ERR_OK) {
            return ["success" => false, 'message' => 'Upload photo error, ulangi lagi!'];
        }

        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($photo['type'], $allowedTypes)) {
            return ["success" => false, 'message' => "File photo harus: image/jpg, image/jpeg, image/png, image/webp"];
        }

        $maxSize = 5 * 1024 * 1024;
        if ($photo['size'] > $maxSize) {
            return ["success" => false, 'message' => "File photo terlalu besar, maksimal 5MB"];
        }

        $photoFromInput = "/ProductImage/" . uniqid() . '-' . basename($photo['name']);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $photoFromInput;
        $isMoveFile = move_uploaded_file($photo['tmp_name'], $filePath);

        if (!$isMoveFile) {
            return ["success" => false, 'message' => "Gagal upload photo, silahkan ulangin lagi!"];
        }

        return ["success" => true, 'message' => "Berhasil upload photo!", 'file' => $photoFromInput];
    }

    public static function create($data): array
    {
        $connect = Database::connect();

        try {
            $stmt = $connect->prepare('INSERT INTO products (name, category_id, description, price, image) VALUES (?, ?, ?, ?, ?)')
                ?: throw new \Exception("Gagal mempersiapkan statement.");

            $name = strtolower(trim($data['name']));
            $category_id = (int) $data['category_id'];
            $description = strtolower(trim($data['description']));
            $price = (int) $data['price'];
            $photo = $data['photo'];

            $uploadPhoto = self::uploadPhoto($photo);
            if (!$uploadPhoto['success']) {
                return Response::failure($uploadPhoto['message']);
            }

            $stmt->bind_param('sisis', $name, $category_id, $description, $price, $uploadPhoto['file']);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                return Response::failure("Gagal menambah data product!");
            }

            return Response::success("Berhasil menambah data product!");
        } catch (\mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return Response::failure("Product Name '{$data['name']}' sudah ada.");
            }

            return Response::failure("Kesalahan database: " . $e->getMessage());
        } catch (\Exception $e) {
            return Response::failure($e->getMessage());
        } finally {
            $connect->close();
        }
    }


    public static function update($paramProductsID, $data): array
    {
        $connect = Database::connect();

        // try {
        $name = $data['name'];
        $category_id = $data['category_id'];
        $description = $data['description'];
        $price = $data['price'];

        var_dump($data['photo']);

        if (!$data['photo']) {
            $query = "UPDATE products SET name = ?, category_id = ?, description = ?, price = ? WHERE id = ?";
            $stmt = $connect->prepare($query) ?: throw new \Exception("Error: gagal membuat statement");
            $stmt->bind_param("sisii", $name, $category_id, $description, $price, $paramProductsID);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                return Response::failure("Gagal update: tidak ada perubahan data product!");
            }

            $connect->close();
            return Response::success("Berhasil update data product!");
        }

        $queryFindOldPhoto = "SELECT image FROM products WHERE id = ?";
        $stmtFindOldPhoto = $connect->prepare($queryFindOldPhoto) ?: throw new \Exception("Error: gagal membuat statement");
        $stmtFindOldPhoto->bind_param('i', $paramProductsID);
        $stmtFindOldPhoto->execute();

        $resultFindOldPhoto = $stmtFindOldPhoto->get_result();
        $oldPhoto = $resultFindOldPhoto->fetch_assoc();

        $oldPhotoPath = $_SERVER['DOCUMENT_ROOT'] . $oldPhoto['image'];

        $query = "UPDATE products SET name = ?, category_id = ?, description = ?, price = ?, image = ? WHERE id = ?";
        $stmt = $connect->prepare($query) ?: throw new \Exception("Error: gagal membuat statement");

        $uploadPhoto = self::uploadPhoto($data['photo']);
        if (!$uploadPhoto['success']) {
            return Response::failure($uploadPhoto['message']);
        }

        $stmt->bind_param("sisisi", $name, $category_id, $description, $price, $uploadPhoto['file'], $paramProductsID);

        $stmt->execute();
        if ($stmt->affected_rows <= 0) {
            return Response::failure("Gagal update: tidak ada perubahan data product [with photo]!");
        }

        if (file_exists($oldPhotoPath)) {
            $isDeleteOldPhoto = unlink($oldPhotoPath);

            if (!$isDeleteOldPhoto) {
                return Response::failure("Gagal hapus photo lama, tapi Berhasil  Update data product!");
            }
        }

        $connect->close();
        return Response::success("Berhasil update data product!");
        // } catch (\mysqli_sql_exception $e) {
        //     if (str_contains($e->getMessage(), 'Duplicate entry')) {
        //         return Response::failure("Nama product '{$data['name']}' sudah ada.");
        //     }

        //     return Response::failure("Kesalahan database: " . $e->getMessage());
        // } catch (\Exception $e) {
        //     return Response::failure($e->getMessage());
        // } finally {
        //     $connect->close();
        // }
    }

    public static function softDelete($paramProductsID)
    {
        $connect = Database::connect();

        try {
            $stmt = $connect->prepare('UPDATE products SET deleted_at = NOW() WHERE id = ?')
                ?: throw new \Exception("Gagal mempersiapkan statement.");

            $stmt->bind_param('i', $paramProductsID);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                return Response::failure("Gagal menambah data product!");
            }

            return Response::success("Berhasil menambah data product!");
        } catch (\mysqli_sql_exception $e) {
            return Response::failure("Kesalahan database: " . $e->getMessage());
        } catch (\Exception $e) {
            return Response::failure($e->getMessage());
        } finally {
            $connect->close();
        }
    }
}