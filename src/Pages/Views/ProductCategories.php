<?php
namespace App\Pages\Views;
use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use App\Utils\Message;
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

    private function getProductCategoriesRows()
    {
        $queryDatas = $this->connect->query("SELECT * FROM product_categories WHERE deleted_at IS NULL");
        $productCategoriesDatas = $queryDatas->fetch_all(MYSQLI_ASSOC);

        if (count($productCategoriesDatas) < 1) {
            return "<tr><td colspan='100%' style='border: 1px solid black; padding:5px 10px' align='center'>Empty Data</td></tr>";
        }

        $rows = [];

        foreach ($productCategoriesDatas as $key => $row) {
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
        <form method="POST" action="/dashboard/product-categories/q/<?= $id ?>">
            <input type="hidden" name="_SECURITY_" value="1234567890">
            <input type="hidden" name="_METHOD_" value="DELETE">
            <button type="submit">DELETE</button>
        </form>

        <!-- EDIT -->
        <form method="GET" action="/dashboard/product-categories/q/<?= $id ?>">
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
            <section>
                <form action="<?= $formActions ?>" method="POST">
                    <input type="hidden" name="_SECURITY_" value="1234567890">
                    <?php if ($this->paramProductCategoriesID): ?>
                        <input type="hidden" name="_METHOD_" value="PUT">
                    <?php endif ?>
                    <input type="text" name="name" value="<?= $result['name'] ?? '' ?>" placeholder="Role name"
                        autocorrect="off" autocomplete="off">
                    <button type="submit"><?= $btnUpdateCategories ?></button>
                </form>
            </section>
            <section>
                <table>
                    <?= $this->getProductCategoriesCols() ?>
                    <?= $this->getProductCategoriesRows() ?>
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
        <?= Dashboard::get(ob_get_clean(), 'Categories');
    }

    public function __destruct()
    {
        $this->connect->close();
    }

} ?>