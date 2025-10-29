<?php

namespace App\Models;

use App\Config\Database;
use App\Utils\Response;

class LoginModel
{
    public static function post($data)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $connect = Database::connect();
        try {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("s", $data['email']) ?: throw new \Exception("Error: Gagal melakukan prepare");

            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                return Response::failure($user);
            }

            if ($user['password'] !== $data['password']) {
                return Response::failure("Email atau Password salah");
            }

            $_SESSION['_USER_'] = [
                "id" => $user['id'],
                "name" => $user['name'],
            ];

            return Response::success("Berhasil Login");
        } catch (\mysqli_sql_exception $e) {
            return Response::failure("Error: Error Nih! Hubungi Developer");
        } catch (\Exception $err) {
            return Response::failure("Error Banget: Error Nih! Hubungi Developer");
        } finally {
            $connect->close();
        }
    }

}
