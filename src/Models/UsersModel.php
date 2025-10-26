<?php
namespace App\Models;

use App\Config\Database;
use App\Utils\Response;

class UsersModel
{
    public static function create($data): array
    {
        $connect = Database::connect();

        try {
            $stmt = $connect->prepare('INSERT INTO users (name, role_id, email, password) VALUES (?, ?, ?, ?)')
                ?: throw new \Exception("Gagal mempersiapkan statement.");

            $name = strtolower(trim($data['name']));
            $role_id = (int) $data['role_id'];
            $email = strtolower(trim($data['email']));
            $password = $data['password'];

            $stmt->bind_param('siss', $name, $role_id, $email, $password);
            $stmt->execute();

            if ($stmt->affected_rows <= 0) {
                return Response::failure("Gagal menambah data name!");
            }

            return Response::success("Berhasil menambah data name!");
        } catch (\mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return Response::failure("Email '{$data['email']}' sudah ada.");
            }

            return Response::failure("Kesalahan database: " . $e->getMessage());
        } catch (\Exception $e) {
            return Response::failure($e->getMessage());
        } finally {
            $connect->close();
        }
    }


    public static function update($paramUsersID, $data): array
    {
        $connect = Database::connect();

        try {
            $password = $data['password'];

            $query = $password
                ? 'UPDATE users SET name = ?, role_id = ?, email = ?, password = ? WHERE id = ?'
                : 'UPDATE users SET name = ?, role_id = ?, email = ? WHERE id = ?';
            var_dump($query);
            $stmt = $connect->prepare($query) ?: throw new \Exception("Gagal mempersiapkan statement.");

            $name = strtolower(trim($data['name']));
            $role_id = (int) $data['role_id'];
            $email = strtolower(trim($data['email']));

            if ($password) {
                $stmt->bind_param("sissi", $name, $role_id, $email, $password, $paramUsersID);
            } else {
                $stmt->bind_param("sisi", $name, $role_id, $email, $paramUsersID);
            }

            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                return Response::success("Berhasil menambah data name!");
            }

            return Response::failure("Gagal menambah data name!");
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

    public static function softDelete($paramUsersID)
    {
        $connect = Database::connect();

        try {
            $stmt = $connect->prepare('UPDATE users SET deleted_at = NOW() WHERE id = ?')
                ?: throw new \Exception("Gagal mempersiapkan statement.");

            $stmt->bind_param('i', $paramUsersID);
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