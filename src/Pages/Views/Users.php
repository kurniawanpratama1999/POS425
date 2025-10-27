<?php

namespace App\Pages\Views;

use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use App\Utils\Message;
use DateTime;
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
        <tr>
            <th class='text-left'>row</th>
            <th class='text-left'>name</th>
            <th class='text-center'>role name</th>
            <th class='text-center'>email</th>
            <th class='text-left'>created at</th>
            <th class='text-left'>updated at</th>
            <th class='text-center'>actions</th>
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

            $no = str_pad($key, 2, "0", STR_PAD_LEFT);

            $bg_role_name = "";
            switch (strtolower($row['role_name'])) {
                case 'superadmin':
                    $bg_role_name = 'bg-emerald-100 text-emerald-600';
                    break;
                case 'admin':
                    $bg_role_name = 'bg-blue-100 text-blue-600';
                    break;
                case 'operator':
                    $bg_role_name = 'bg-yellow-100 text-yellow-600';
                    break;

                default:
                    $bg_role_name = 'bg-purple-100 text-purple-600';
                    break;
            }

            $created_at = new DateTime($row['created_at']);
            $created_at_date = date_format($created_at, 'd/m/Y');
            $created_at_time = date_format($created_at, 'h:i:s');

            $updated_at = new DateTime($row['updated_at']);
            $updated_at_date = date_format($updated_at, 'd/m/Y');
            $updated_at_time = date_format($updated_at, 'h:i:s');

            $rows[] = "
            <tr>
                <td>$no</td>
                <td class='uppercase'>{$row['name']}</td>
                <td>
                    <div class='flex items-center justify-center text-sm'>
                        <p class='w-fit px-4 py-2 font-mono rounded-full $bg_role_name'>{$row['role_name']}</p>
                    </div>
                </td>
                <td>
                    <div class='flex items-center justify-center font-mono'>
                        <p class='text-sm text-black px-4 py-2 bg-slate-200 rounded-full w-fit text-center'>{$row['email']}</p>
                    </div>
                </td>
                <td class='text-sm font-mono'>
                    <div class='flex flex-col'>
                        <span>$created_at_date</span>
                        <span>$created_at_time</span>
                    </div>
                </td>
                <td class='text-sm font-mono'>
                    <div class='flex flex-col'>
                        <span>$updated_at_date</span>
                        <span>$updated_at_time</span>
                    </div>
                </td>
                <td><div id='edit-and-delete-{$row['id']}'>{$this->editAndDelete($row['id'])}</div></td>
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
        <form id="button-delete-<?= $id ?>" method="POST" action="/dashboard/users/q/<?= $id ?>">
            <input type="hidden" name="_SECURITY_" value="1234567890">
            <input type="hidden" name="_METHOD_" value="DELETE">
            <button type="submit">DELETE</button>
        </form>

        <!-- EDIT -->
        <a id="button-edit-<?= $id ?>" href="/dashboard/users/q/<?= $id ?>">EDIT</a>

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
                        class="<?= $message['success'] ? "bg-emerald-200" : "bg-red-200" ?> fixed top-14 left-1/2 -transalate-x-1/2">
                        <?= $message['message'] ?>
                    </span>
                <?php endif ?>
            </section>
            <div>
                <section id="wrapper-add-and-update"
                    class="hidden items-center justify-center fixed top-0 left-0 w-full h-full bg-slate-100/20 backdrop-blur-md">
                    <form id="add-and-update" action="<?= $formActions ?>" method="POST">
                        <h2 class="text-black text-center text-2xl font-bold font-serif">
                            <?= !$this->id ? "TAMBAH" : "EDIT" ?> USER
                        </h2>
                        <input type="hidden" name="_SECURITY_" value="1234567890">
                        <?php if ($this->id): ?>
                            <input type="hidden" name="_METHOD_" value="PUT">
                        <?php endif ?>

                        <label for="name">
                            <span>Full Name <small>*</small></span>
                            <input type="text" name="name" value="<?= $result['name'] ?? '' ?>" placeholder="Fullname"
                                autocorrect="off" autocomplete="off">
                        </label>

                        <label for="role_id">
                            <span>Role Name <small>*</small></span>
                            <select name="role_id">
                                <option value="" <?= $this->id ? "" : "selected" ?>>-- Pilih Role --</option>
                                <?php
                                $getRoleIDfromResult = $result['role_id'] ?? 0;
                                ?>
                                <?php foreach ($stmtRolesResult as $rolesResult): ?>
                                    <option value="<?= $rolesResult['id'] ?? '' ?>" <?= $rolesResult['id'] == $getRoleIDfromResult ? "selected" : "" ?>><?= $rolesResult['name'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </label>

                        <label for="email">
                            <span>Email <small>*</small></span>
                            <input type="text" name="email" value="<?= $result['email'] ?? '' ?>" placeholder="your@email.com"
                                autocorrect="off" autocomplete="off">
                        </label>

                        <label for="password">
                            <span>Password <small>*</small></span>
                            <input type="text" name="password" value="" placeholder="********" autocorrect="off"
                                autocomplete="off">
                        </label>

                        <div class="wrapper-button">
                            <a class="bg-red-100 text-red-600" href="/dashboard/users">CANCEL</a>
                            <button class="<?= !$this->id ? "bg-emerald-100 text-emerald-600" : "bg-blue-100 text-blue-600" ?>"
                                type="submit"><?= $btnUpdateUsers ?></button>
                        </div>
                    </form>
                </section>
                <section>
                    <div class="max-w-dvw overflow-x-auto">
                        <div class="flex flex-row justify-between p-2">
                            <label for="search" id="search" class="bg-slate-100 p-3 rounded">
                                <input type="text" name="search" placeholder="search user">
                            </label>
                            <button onclick="toggleFormAddAndUpdate()" type="button" id="button-new">New User</button>
                        </div>
                        <table class="w-full">
                            <?= $this->getUserRolesCols() ?>
                            <?= $this->getUserRolesRows() ?>
                        </table>
                    </div>
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

            const elementWrapperAddAndUpdate = document.getElementById('wrapper-add-and-update');
            const path = window.location.pathname;

            if (path.match("/users/q")) {
                elementWrapperAddAndUpdate.classList.replace("hidden", 'flex');
            }

            const toggleFormAddAndUpdate = () => {
                elementWrapperAddAndUpdate.classList.toggle("hidden");
                elementWrapperAddAndUpdate.classList.toggle("flex");
            }
        </script>
        <?= Dashboard::get(ob_get_clean(), "Users");
    }

    public function __destruct()
    {
        $this->connect->close();
    }
} ?>