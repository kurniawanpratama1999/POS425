<?php

namespace App\Pages\Views;

use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use App\Utils\Message;
use DateTime;

?>

<?php class Products
{
    private ?int $paramProductsID;
    private $connect;

    public function __construct(?int $paramProductsID = null)
    {
        $this->paramProductsID = $paramProductsID;
        $this->connect = Database::connect();
    }

    private function getProductsCols()
    {
        $style = "border: 1px solid black; padding:5px 10px;";
        return "
        <tr>
            <th class='text-left'>row</th>
            <th class='text-left'>name</th>
            <th class='text-left'>category</th>
            <th class='text-left'>description</th>
            <th class='text-center'>price</th>
            <th class='text-left'>photo</th>
            <th class='text-left'>created at</th>
            <th class='text-left'>updated at</th>
            <th class='text-center'>actions</th>
        </tr>
        ";
    }

    private function getProductsRows()
    {
        $queryDatas = $this->connect->query("SELECT p.*, pc.name AS category_name FROM products AS p JOIN product_categories AS pc ON p.category_id = pc.id WHERE p.deleted_at IS NULL");
        $ProductsDatas = $queryDatas->fetch_all(MYSQLI_ASSOC);

        if (count($ProductsDatas) < 1) {
            return "<tr><td colspan='100%' class='text-center'>Empty Data</td></tr>";
        }

        $rows = [];

        foreach ($ProductsDatas as $key => $row) {
            $key += 1;
            $no = str_pad($key, 2, "0", STR_PAD_LEFT);

            $bg_role_name = "";
            switch (strtolower($row['category_name'])) {
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

            $price = number_format($row['price'], 0, ",", ".");

            $created_at = new DateTime($row['created_at']);
            $created_at_date = date_format($created_at, 'd/m/Y');
            $created_at_time = date_format($created_at, 'h:i:s');

            $updated_at = new DateTime($row['updated_at']);
            $updated_at_date = date_format($updated_at, 'd/m/Y');
            $updated_at_time = date_format($updated_at, 'h:i:s');

            $rows[] = "
            <tr>
                <td>$key</td>
                <td>{$row['name']}</td>
                <td>
                    <div class='flex items-center justify-center text-sm'>
                        <p class='w-fit px-4 py-2 font-mono rounded-full $bg_role_name'>{$row['category_name']}</p>
                    </div>
                </td>
                <td>{$row['description']}</td>
                <td>
                <div class='flex items-center justify-center font-mono'>
                    <p class='text-sm text-black px-4 py-2 bg-slate-200 rounded-full w-fit text-center text-nowrap'>Rp {$price}</p>
                </div>
                </td>
                <td>{$row['photo']}</td>
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
        <form id="button-delete-<?= $id ?>" method="POST" action="/dashboard/products/q/<?= $id ?>">
            <input type="hidden" name="_SECURITY_" value="1234567890">
            <input type="hidden" name="_METHOD_" value="DELETE">
            <button type="submit">DELETE</button>
        </form>

        <!-- EDIT -->
        <form id="button-edit-<?= $id ?>" method="GET" action="/dashboard/products/q/<?= $id ?>">
            <button type="submit">EDIT</button>
        </form>
    <?php
        return ob_get_clean();
    }

    public function render()
    {
        //! MENAMPILKAN OPTIONS
        $stmtCategories = $this->connect->query("SELECT * FROM product_categories WHERE deleted_at IS NULL");
        $stmtCategoriesResult = $stmtCategories->fetch_all(MYSQLI_ASSOC);

        if (!empty($this->paramProductsID)) {
            $stmt = $this->connect->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param('i', $this->paramProductsID);
            $stmt->execute();

            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        $btnUpdateProducts = !$this->paramProductsID ? "ADD PRODUCT" : "EDIT PRODUCT";
        $formActions = !$this->paramProductsID ? "/dashboard/products" : "/dashboard/products/q/$this->paramProductsID";

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
                <form id="add-and-update" action="<?= $formActions ?>" method="POST" enctype="multipart/form-data">
                    <h2 class="text-black text-center text-2xl font-bold font-serif">
                        <?= !$this->paramProductsID ? "TAMBAH" : "EDIT" ?> PRODUCT
                    </h2>
                    <input type="hidden" name="_SECURITY_" value="1234567890">
                    <?php if ($this->paramProductsID): ?>
                        <input type="hidden" name="_METHOD_" value="PUT">
                    <?php endif ?>

                    <label for="name">
                        <span>Product Name <small>*</small></span>
                        <input type="text" name="name" value="<?= $result['name'] ?? '' ?>" placeholder="Product Name"
                            autocorrect="off" autocomplete="off">
                    </label>

                    <label for="category_id">
                        <span>Category Name <small>*</small></span>
                        <select name="category_id">
                            <option value="" <?= $this->paramProductsID ? "" : "selected" ?>>-- Pilih Category --</option>
                            <?php
                            $getRoleIDfromResult = $result['category_id'] ?? 0;
                            ?>
                            <?php foreach ($stmtCategoriesResult as $categoryResult): ?>
                                <option value="<?= $categoryResult['id'] ?? '' ?>" <?= $categoryResult['id'] == $getRoleIDfromResult ? "selected" : "" ?>>
                                    <?= $categoryResult['name'] ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </label>

                    <label for="description">
                        <span>Description Product <small>*</small></span>
                        <input type="text" name="description" value="<?= $result['description'] ?? '' ?>"
                            placeholder="Product Description" autocorrect="off" autocomplete="off">
                    </label>

                    <label for="price">
                        <span>Price <small>*</small></span>
                        <input type="text" name="price" value="<?= $result['price'] ?? '' ?>" placeholder="Product Price"
                            autocorrect="off" autocomplete="off">
                    </label>

                    <label for="photo">
                        <span>Photo <small>*</small></span>
                        <input type="file" name="photo" accept="image/*">
                    </label>

                    <div class="wrapper-button">
                        <a class="bg-red-100 text-red-600" href="/dashboard/products">CANCEL</a>
                        <button class="<?= !$this->paramProductsID ? "bg-emerald-100 text-emerald-600" : "bg-blue-100 text-blue-600" ?>"
                            type="submit"><?= $btnUpdateProducts ?></button>
                    </div>
                </form>
            </section>
            <section>
                <div class="max-w-dvw overflow-x-auto">
                    <div class="flex flex-row justify-between p-2">
                        <label for="search" id="search" class="bg-slate-100 p-3 rounded">
                            <input type="text" name="search" placeholder="search user">
                        </label>
                        <button onclick="toggleFormAddAndUpdate()" type="button" id="button-new">New Product</button>
                    </div>
                    <table class="w-full">
                        <?= $this->getProductsCols() ?>
                        <?= $this->getProductsRows() ?>
                    </table>
                </div>
            </section>
        </div>
        <script>
            const messageElement = document.getElementById('message');
            if (messageElement) {
                setTimeout(() => {
                    messageElement.remove()
                }, 5000);
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
<?= Dashboard::get(ob_get_clean(), 'Products');
    }

    public function __destruct()
    {
        $this->connect->close();
    }
} ?>