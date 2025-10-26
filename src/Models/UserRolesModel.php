<?php
namespace App\Models;

use App\Config\Database;
use App\Utils\Response;

class UserRolesModel
{
    public static function create($data): array
    {
        $connect = Database::connect();

        try {
            $stmt = $connect->prepare('INSERT INTO user_roles (name) VALUES (?)')
                ?: throw new \Exception("Gagal mempersiapkan statement.");

            $name = strtolower(trim($data['name']));
            $stmt->bind_param('s', $name);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                return Response::failure("Gagal menambah data name!");
            }

            return Response::success("Berhasil menambah data name!");
        } catch (\mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return Response::failure("Nama role '{$data['name']}' sudah ada.");
            }

            return Response::failure("Kesalahan database: " . $e->getMessage());
        } catch (\Exception $e) {
            return Response::failure($e->getMessage());
        } finally {
            $connect->close();
        }
    }


    public static function update($id, $data): array
    {
        $connect = Database::connect();

        try {
            $stmt = $connect->prepare('UPDATE user_roles SET name = ? WHERE id = ?')
                ?: throw new \Exception("Gagal mempersiapkan statement.");

            $name = strtolower(trim($data['name']));
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                return Response::failure("Gagal menambah data name!");
            }

            return Response::success("Berhasil menambah data name!");
        } catch (\mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return Response::failure("Nama role '{$data['name']}' sudah ada.");
            }

            return Response::failure("Kesalahan database: " . $e->getMessage());
        } catch (\Exception $e) {
            return Response::failure($e->getMessage());
        } finally {
            $connect->close();
        }
    }

    public static function softDelete($id)
    {
        $connect = Database::connect();

        try {
            $stmt = $connect->prepare('UPDATE user_roles SET deleted_at = NOW() WHERE id = ?')
                ?: throw new \Exception("Gagal mempersiapkan statement.");

            $stmt->bind_param('i', $id);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                return Response::failure("Gagal menambah data name!");
            }

            return Response::success("Berhasil menambah data name!");
        } catch (\mysqli_sql_exception $e) {
            return Response::failure("Kesalahan database: " . $e->getMessage());
        } catch (\Exception $e) {
            return Response::failure($e->getMessage());
        } finally {
            $connect->close();
        }
    }
}