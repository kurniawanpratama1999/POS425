<?php

namespace App\Pages\Views;

use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use App\Utils\Message;
?>

<?php class Users
{
    private ?int $id;
    private $connect;


    public function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->connect = Database::connect();
    }

    private function getUserRolesCols()
    {
        return "
        <tr class='[&_th]:border [&_th]:px-1.5 [&_th]:py-3 bg-emerald-200'>
            <th>row</th>
            <th>name</th>
            <th>role name</th>
            <th>email</th>
            <th>created at</th>
            <th>updated at</th>
            <th>actions</th>
        </tr>
        ";
    }

    private function getUserRolesRows()
    {
        $queryDatas = $this->connect->query("SELECT u.*, ur.name AS role_name FROM users AS u JOIN user_roles AS ur ON u.role_id = ur.id  WHERE u.deleted_at IS NULL");
        $userRolesDatas = $queryDatas->fetch_all(MYSQLI_ASSOC);

        if (count($userRolesDatas) < 1) {
            return "<tr><td colspan='100%' style='border: 1px solid black; padding:5px 10px' align='center'>Empty Data</td></tr>";
        }

        $rows = [];

        foreach ($userRolesDatas as $key => $row) {
            $key += 1;

            $rows[] = "
            <tr>
                <td style='border: 1px solid black'>$key</td>
                <td style='border: 1px solid black'>{$row['name']}</td>
                <td style='border: 1px solid black'>{$row['role_name']}</td>
                <td style='border: 1px solid black'>{$row['email']}</td>
                <td style='border: 1px solid black'>{$row['created_at']}</td>
                <td style='border: 1px solid black'>{$row['updated_at']}</td>
                <td style='border: 1px solid black'>{$this->editAndDelete($row['id'])}</td>
            </tr>
            ";
        }

        // gabungkan semua baris
        return implode("\n", $rows);
    }

    private function editAndDelete($id)
    {
        ob_start(); ?>

        <!-- DELETE -->
        <form method="POST" action="/dashboard/users/q/<?= $id ?>">
            <input type="hidden" name="_SECURITY_" value="1234567890">
            <input type="hidden" name="_METHOD_" value="DELETE">
            <button type="submit">DELETE</button>
        </form>

        <!-- EDIT -->
        <a href="/dashboard/users/q/<?= $id ?>">EDIT</a>

    <?php
        return ob_get_clean();
    }

    public function render()
    {
        //! MENAMPILKAN OPTIONS
        $stmtRoles = $this->connect->query("SELECT * FROM user_roles WHERE deleted_at IS NULL");
        $stmtRolesResult = $stmtRoles->fetch_all(MYSQLI_ASSOC);


        if (!empty($this->id)) {
            $stmt = $this->connect->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();

            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        $btnUpdateUsers = !$this->id ? "ADD USERS" : "EDIT USERS";

        $formActions = !$this->id ? "/dashboard/users" : "/dashboard/users/q/$this->id";

        $message = Message::get();
        ob_start() ?>
        <div class="">
            <section>
                <?php if ($message): ?>
                    <span id="message" class="<?= $message['success'] ? "bg-emerald-200" : "bg-red-200" ?> fixed top-14 left-1/2 -transalate-x-1/2">
                        <?= $message['message'] ?>
                    </span>
                <?php endif ?>
            </section>
            <div class="flex flex-row">
                <section class="order-1 basis-1/3">
                    <form action="<?= $formActions ?>" method="POST">
                        <input type="hidden" name="_SECURITY_" value="1234567890">
                        <?php if ($this->id): ?>
                            <input type="hidden" name="_METHOD_" value="PUT">
                        <?php endif ?>

                        <input type="text" name="name" value="<?= $result['name'] ?? '' ?>" placeholder="Fullname" autocorrect="off"
                            autocomplete="off">

                        <select name="role_id">
                            <option value="" <?= $this->id ? "" : "selected" ?>>-- Pilih Role --</option>
                            <?php
                            $getRoleIDfromResult = $result['role_id'] ?? 0;
                            ?>
                            <?php foreach ($stmtRolesResult as $rolesResult): ?>
                                <option value="<?= $rolesResult['id'] ?? '' ?>" <?= $rolesResult['id'] == $getRoleIDfromResult ? "selected" : "" ?>><?= $rolesResult['name'] ?></option>
                            <?php endforeach ?>
                        </select>

                        <input type="text" name="email" value="<?= $result['email'] ?? '' ?>" placeholder="your@email.com"
                            autocorrect="off" autocomplete="off">

                        <input type="text" name="password" value="" placeholder="********" autocorrect="off" autocomplete="off">

                        <button type="submit"><?= $btnUpdateUsers ?></button>
                    </form>
                </section>
                <section class="order-2 basis-2/3 p-3">
                    <table class="w-full">
                        <?= $this->getUserRolesCols() ?>
                        <?= $this->getUserRolesRows() ?>
                    </table>
                </section>
            </div>
        </div>
        <script>
            const messageElement = document.getElementById('message');
            if (messageElement) {
                setTimeout(() => {
                    messageElement.remove()
                }, 1500);
            }
        </script>
<?= Dashboard::get(ob_get_clean(), "Users");
    }

    public function __destruct()
    {
        $this->connect->close();
    }
} ?>