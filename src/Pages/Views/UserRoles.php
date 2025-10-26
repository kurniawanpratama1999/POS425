<?php
namespace App\Pages\Views;
use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use App\Utils\Message;
?>

<?php class UserRoles
{
    private ?int $paramUserRolesID;
    private $connect;

    public function __construct(?int $paramUserRolesID = null)
    {
        $this->paramUserRolesID = $paramUserRolesID;
        $this->connect = Database::connect();
    }

    private function getUserRolesCols()
    {
        $style = "border: 1px solid black; padding:5px 10px;";
        return "
        <tr>
            <th style='$style text-align:left;'>row</th>
            <th style='$style text-align:left;'>name</th>
            <th style='$style text-align:left;'>created at</th>
            <th style='$style text-align:left;'>updated at</th>
            <th style='$style text-align:center;'>actions</th>
        </tr>
        ";
    }

    private function getUserRolesRows()
    {
        $queryDatas = $this->connect->query("SELECT * FROM user_roles WHERE deleted_at IS NULL");
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
                <td style='border: 1px solid black'>{$row['created_at']}</td>
                <td style='border: 1px solid black'>{$row['updated_at']}</td>
                <td style='border: 1px solid black'>{$this->compEditAndDeleteButton($row['id'])}</td>
            </tr>
            ";
        }

        // gabungkan semua baris
        return implode("\n", $rows);
    }

    private function compEditAndDeleteButton($id)
    {
        ob_start(); ?>
        <!-- DELETE -->
        <form method="POST" action="/dashboard/user-roles/q/<?= $id ?>">
            <input type="hidden" name="_SECURITY_" value="1234567890">
            <input type="hidden" name="_METHOD_" value="DELETE">
            <button type="submit">DELETE</button>
        </form>

        <!-- EDIT -->
        <form method="GET" action="/dashboard/user-roles/q/<?= $id ?>">
            <button type="submit">EDIT</button>
        </form>
        <?php
        return ob_get_clean();
    }

    public function render()
    {
        if (!empty($this->paramUserRolesID)) {
            $stmt = $this->connect->prepare("SELECT * FROM user_roles WHERE id = ?");
            $stmt->bind_param('i', $this->paramUserRolesID);
            $stmt->execute();

            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        $btnUpdateRole = !$this->paramUserRolesID ? "ADD ROLE" : "EDIT ROLE";
        $formActions = !$this->paramUserRolesID ? "/dashboard/user-roles" : "/dashboard/user-roles/q/$this->paramUserRolesID";

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
                    <?php if ($this->paramUserRolesID): ?>
                        <input type="hidden" name="_METHOD_" value="PUT">
                    <?php endif ?>
                    <input type="text" name="name" value="<?= $result['name'] ?? '' ?>" placeholder="Role name"
                        autocorrect="off" autocomplete="off">
                    <button type="submit"><?= $btnUpdateRole ?></button>
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
                }, 1000);
            }
        </script>
        <?= Dashboard::get(ob_get_clean(), 'Roles');
    }

    public function __destruct()
    {
        $this->connect->close();
    }

} ?>