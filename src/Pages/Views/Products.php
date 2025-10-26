<?php
namespace App\Pages\Views;
use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use App\Utils\Message;
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
            <th style='$style text-align:left;'>row</th>
            <th style='$style text-align:left;'>name</th>
            <th style='$style text-align:left;'>category</th>
            <th style='$style text-align:left;'>description</th>
            <th style='$style text-align:left;'>price</th>
            <th style='$style text-align:left;'>photo</th>
            <th style='$style text-align:left;'>created at</th>
            <th style='$style text-align:left;'>updated at</th>
            <th style='$style text-align:center;'>actions</th>
        </tr>
        ";
    }

    private function getProductsRows()
    {
        $queryDatas = $this->connect->query("SELECT * FROM products WHERE deleted_at IS NULL");
        $ProductsDatas = $queryDatas->fetch_all(MYSQLI_ASSOC);

        if (count($ProductsDatas) < 1) {
            return "<tr><td colspan='100%' style='border: 1px solid black; padding:5px 10px' align='center'>Empty Data</td></tr>";
        }

        $rows = [];

        foreach ($ProductsDatas as $key => $row) {
            $key += 1;

            $rows[] = "
            <tr>
                <td style='border: 1px solid black'>$key</td>
                <td style='border: 1px solid black'>{$row['name']}</td>
                <td style='border: 1px solid black'>{$row['category_id']}</td>
                <td style='border: 1px solid black'>{$row['description']}</td>
                <td style='border: 1px solid black'>{$row['price']}</td>
                <td style='border: 1px solid black'>{$row['image']}</td>
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
        <form method="POST" action="/dashboard/products/q/<?= $id ?>">
            <input type="hidden" name="_SECURITY_" value="1234567890">
            <input type="hidden" name="_METHOD_" value="DELETE">
            <button type="submit">DELETE</button>
        </form>

        <!-- EDIT -->
        <form method="GET" action="/dashboard/products/q/<?= $id ?>">
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
        <?= $_SERVER['DOCUMENT_ROOT'] . '/ProductImage/' ?>
        <div>
            <section>
                <?php if ($message): ?>
                    <span id="message"
                        style="color: <?= $message['success'] ? "green" : "red" ?>; font-weight: bold;"><?= $message['message'] ?></span>
                <?php endif ?>
            </section>
            <section>
                <form action="<?= $formActions ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_SECURITY_" value="1234567890">
                    <?php if ($this->paramProductsID): ?>
                        <input type="hidden" name="_METHOD_" value="PUT">
                    <?php endif ?>

                    <input type="text" name="name" value="<?= $result['name'] ?? '' ?>" placeholder="Product Name"
                        autocorrect="off" autocomplete="off">

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

                    <input type="text" name="description" value="<?= $result['description'] ?? '' ?>"
                        placeholder="Product Description" autocorrect="off" autocomplete="off">

                    <input type="text" name="price" value="<?= $result['price'] ?? '' ?>" placeholder="Product Price"
                        autocorrect="off" autocomplete="off">

                    <input type="file" name="photo" accept="image/*">

                    <button type="submit"><?= $btnUpdateProducts ?></button>
                </form>
            </section>
            <section>
                <table>
                    <?= $this->getProductsCols() ?>
                    <?= $this->getProductsRows() ?>
                </table>
            </section>
        </div>
        <script>
            const messageElement = document.getElementById('message');
            if (messageElement) {
                setTimeout(() => {
                    messageElement.remove()
                }, 5000);
            }
        </script>
        <?= Dashboard::get(ob_get_clean(), 'Categories');
    }

    public function __destruct()
    {
        $this->connect->close();
    }

} ?>