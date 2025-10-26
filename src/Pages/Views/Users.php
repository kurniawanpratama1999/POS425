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
        $style = "border: 1px solid black; padding:5px 10px;";
        return "
        <tr>
            <th style='$style text-align:left;'>row</th>
            <th style='$style text-align:left;'>name</th>
            <th style='$style text-align:left;'>role name</th>
            <th style='$style text-align:left;'>email</th>
            <th style='$style text-align:left;'>created at</th>
            <th style='$style text-align:left;'>updated at</th>
            <th style='$style text-align:center;'>actions</th>
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
        <div>
            <section>
                <?php if ($message): ?>
                    <span id="message"
                        style="color: <?= $message['success'] ? "green" : "red" ?>; font-weight: bold;"><?= $message['message'] ?></span>
                <?php endif ?>
            </section>
            <section>
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
            <section>
                <table>
                    <?= $this->getUserRolesCols() ?>
                    <?= $this->getUserRolesRows() ?>
                </table>
            </section>
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