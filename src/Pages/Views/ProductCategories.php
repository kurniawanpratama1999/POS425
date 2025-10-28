<?php

namespace App\Pages\Views;

use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use App\Utils\Message;
use DateTime;

?>

<?php class ProductCategories
{
    private ?int $paramProductCategoriesID;
    private $connect;

    public function __construct(?int $paramProductCategoriesID = null)
    {
        $this->paramProductCategoriesID = $paramProductCategoriesID;
        $this->connect = Database::connect();
    }

    private function getProductCategoriesCols()
    {
        return "
        <tr>
            <th class='text-left'>row</th>
            <th class='text-left'>name</th>
            <th class='text-left'>created at</th>
            <th class='text-left'>updated at</th>
            <th class='text-center'>actions</th>
        </tr>
        ";
    }

    private function getProductCategoriesRows()
    {
        $queryDatas = $this->connect->query("SELECT * FROM product_categories WHERE deleted_at IS NULL");
        $productCategoriesDatas = $queryDatas->fetch_all(MYSQLI_ASSOC);

        if (count($productCategoriesDatas) < 1) {
            return "<tr><td colspan='100%' class='text-center'>Empty Data</td></tr>";
        }

        $rows = [];

        foreach ($productCategoriesDatas as $key => $row) {
            $key += 1;
            $no = str_pad($key, 2, "0", STR_PAD_LEFT);
            $created_at = new DateTime($row['created_at']);
            $created_at_date = date_format($created_at, 'd/m/Y');
            $created_at_time = date_format($created_at, 'h:i:s');

            $updated_at = new DateTime($row['updated_at']);
            $updated_at_date = date_format($updated_at, 'd/m/Y');
            $updated_at_time = date_format($updated_at, 'h:i:s');
            $rows[] = "
            <tr>
                <td>$no</td>
                <td>{$row['name']}</td>
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
                <td><div id='edit-and-delete-{$row['id']}'>{$this->compEditAndDeleteButton($row['id'])}</div></td>
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
        <form id="button-delete-<?= $id ?>" method="POST" action="/dashboard/product-categories/q/<?= $id ?>">
            <input type="hidden" name="_SECURITY_" value="1234567890">
            <input type="hidden" name="_METHOD_" value="DELETE">
            <button type="submit">DELETE</button>
        </form>

        <!-- EDIT -->
        <form id="button-edit-<?= $id ?>" method="GET" action="/dashboard/product-categories/q/<?= $id ?>">
            <button type="submit">EDIT</button>
        </form>
    <?php
        return ob_get_clean();
    }

    public function render()
    {
        if (!empty($this->paramProductCategoriesID)) {
            $stmt = $this->connect->prepare("SELECT * FROM product_categories WHERE id = ?");
            $stmt->bind_param('i', $this->paramProductCategoriesID);
            $stmt->execute();

            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        $btnUpdateCategories = !$this->paramProductCategoriesID ? "ADD CATEGORY" : "EDIT CATEGORY";
        $formActions = !$this->paramProductCategoriesID ? "/dashboard/product-categories" : "/dashboard/product-categories/q/$this->paramProductCategoriesID";

        $message = Message::get();
        ob_start() ?>
        <div>
            <section>
                <?php if ($message): ?>
                    <span id="message"
                        style="color: <?= $message['success'] ? "green" : "red" ?>; font-weight: bold;"><?= $message['message'] ?></span>
                <?php endif ?>
            </section>
            <section id="wrapper-add-and-update" class="hidden items-center justify-center fixed top-0 left-0 w-full h-full bg-slate-100/20 backdrop-blur-md">
                <form id="add-and-update" action="<?= $formActions ?>" method="POST">
                    <h2 class="text-black text-center text-2xl font-bold font-serif">
                        <?= !$this->paramProductCategoriesID ? "TAMBAH" : "EDIT" ?> CATEGORY
                    </h2>
                    <input type="hidden" name="_SECURITY_" value="1234567890">
                    <?php if ($this->paramProductCategoriesID): ?>
                        <input type="hidden" name="_METHOD_" value="PUT">
                    <?php endif ?>

                    <label for="name">
                        <span>Category Name <small>*</small></span>
                        <input type="text" name="name" value="<?= $result['name'] ?? '' ?>" placeholder="Role name"
                            autocorrect="off" autocomplete="off">
                    </label>

                    <div class="wrapper-button">
                        <a class="bg-red-100 text-red-600" href="/dashboard/product-categories">CANCEL</a>
                        <button class="<?= !$this->paramProductCategoriesID ? "bg-emerald-100 text-emerald-600" : "bg-blue-100 text-blue-600" ?>"
                            type="submit"><?= $btnUpdateCategories ?></button>
                    </div>
                </form>
            </section>
            <section>
                <div class="max-w-dvw overflow-x-auto">
                    <div class="flex flex-row justify-between p-2">
                        <label for="search" id="search" class="bg-slate-100 p-3 rounded">
                            <input type="text" name="search" placeholder="search user">
                        </label>
                        <button onclick="toggleFormAddAndUpdate()" type="button" id="button-new">New CATEGORY</button>
                    </div>
                    <table class="w-full">
                        <?= $this->getProductCategoriesCols() ?>
                        <?= $this->getProductCategoriesRows() ?>
                    </table>
                </div>
            </section>
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

            if (path.match("/q/")) {
                elementWrapperAddAndUpdate.classList.replace("hidden", 'flex');
            }

            const toggleFormAddAndUpdate = () => {
                elementWrapperAddAndUpdate.classList.toggle("hidden");
                elementWrapperAddAndUpdate.classList.toggle("flex");
            }
        </script>
<?= Dashboard::get(ob_get_clean(), 'Categories');
    }

    public function __destruct()
    {
        $this->connect->close();
    }
} ?>