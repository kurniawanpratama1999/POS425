<?php

namespace App\Pages\Views;

use App\Config\Database;
use App\Pages\Layouts\Dashboard;
use mysqli_sql_exception;

class Transactions
{
    private $connect;

    public function __construct()
    {
        $this->connect = Database::connect();
    }

    public function store()
    {

        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data || !is_array($data)) {
            echo json_encode(["success" => false, "message" => "Data tidak valid"]);
        }

        $this->connect->begin_transaction();
        
        try {
            $queryOrders = "INSERT INTO orders (code, total_amount, payment_amount, change_amount) VALUES (?, ?, ?, ?)";
            $stmtOrders  = $this->connect->prepare($queryOrders) ?: throw new \Exception("Gagal Membuat statement");
            $changeAmount = 0;
            $stmtOrders->bind_param('siii', $data['code'], $data['total_amount'], $data['total_amount'], $changeAmount);
            $stmtOrders->execute();
            if ($stmtOrders->affected_rows <= 0) {
                echo json_encode(["success" => false, "message" => "Gagal Melakukan input ke orders"]);
                exit;
            }

            $orderID = $stmtOrders->insert_id;

            foreach($data['items'] as $items) {
                $queryOrderDetails = "INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)";
                $stmtOrderDetails  = $this->connect->prepare($queryOrderDetails) ?: throw new \Exception("Gagal Membuat statement");
                $subtotal = $items['price'] * $items['val'];
                $stmtOrderDetails->bind_param('iiiii', $orderID, $items['id'], $items['val'], $items['price'], $subtotal);
                $stmtOrderDetails->execute();
                if ($stmtOrderDetails->affected_rows <= 0) {
                    echo json_encode(["success" => false, "message" => "Gagal Melakukan input ke orders"]);
                    exit;
                }

            }

            echo json_encode(["success" => true, "message" => "Data Valid", "data" => $data]);
            $this->connect->commit();
        } catch (mysqli_sql_exception $errSql) {
            echo json_encode(["success" => false, "message" => "Error: Gagal melakukan input"]);
            exit;
        }
    }

    public function print()
    {
        ob_start(); ?>
        <div>a</div>
    <?= ob_get_clean();
    }

    public function render()
    {
        $queryGetProducts = "SELECT p.*, pc.name AS category_name FROM products AS p JOIN product_categories AS pc ON p.id = pc.id WHERE p.deleted_at IS NULL";
        $stmtGetProducts = $this->connect->query($queryGetProducts);
        $resultGetProducts = $stmtGetProducts->fetch_all(MYSQLI_ASSOC);

        ob_start(); ?>
        <div class="flex flex-row h-[calc(100dvh-4rem)] bg-slate-100 print:hidden">

            <div class="basis-2/3 p-3 bg-slate-50">
                <section id="navigation" class="flex flex-row gap-3 items-center justify-center">
                    <?php if (count($resultGetProducts) > 0): ?>
                        <button id="all" class="category-all px-5 w-[100px] outline py-2 rounded-md bg-blue-400 text-white outline-blue-400">All</button>
                        <?php $mappingOnlyGetCategory = array_map(fn($v) => $v['category_name'], $resultGetProducts); ?>
                        <?php foreach (array_unique($mappingOnlyGetCategory) as $category): ?>
                            <button id="<?= $category ?>" class="category-<?= $category ?> w-[100px] px-5 py-2 rounded-md outline outline-blue-400 bg-white text-black">
                                <?= $category ?>
                            </button>
                        <?php endforeach ?>
                    <?php endif ?>
                </section>

                <section id="select-product" class="grid grid-cols-4 gap-3 p-4 place-content-start"></section>
            </div>

            <section class="basis-1/3 grid grid-cols-1 grid-rows-[1fr_auto_auto]">
                <div class="bg-emerald-200 p-2">
                    <div class="grid grid-cols-[1fr_10rem_150px] gap-3 italic font-mono font-semibold">
                        <p>Name</p>
                        <p class="text-center pr-9">qty</p>
                        <p class="text-right">price</p>
                    </div>
                    <div id="counting-product"></div>
                </div>

                <div class="p-5 text-right">
                    <p id="subtotal" class="flex flex-row justify-between items-center">
                        <span>SUBTOTAL</span>
                        <span id="subtotal-val" class="text-xl font-bold">0</span>
                    </p>
                    <p id="pajak" class="flex flex-row justify-between items-center">
                        <span>PAJAK</span>
                        <span id="pajak-val" class="text-xl font-bold">0</span>
                    </p>
                    <p id="total" class="flex flex-row justify-between items-center">
                        <span>TOTAL</span>
                        <span id="total-val" class="text-3xl font-bold">0</span>
                    </p>
                </div>

                <div class="flex flex-row gap-3 p-2">
                    <button id="clear-transaction" class="px-3 py-2 grow bg-red-400 text-white">Clear</button>
                    <button id="payment-transaction" class="px-3 py-2 grow bg-blue-400 text-white">Payments</button>
                </div>
            </section>
        </div>
        <script>
            const products = <?= json_encode($resultGetProducts); ?>;
        </script>
        <script src="/assets/js/transaction.js"></script>
<?= Dashboard::get(ob_get_clean(), "Transaction");
    }
} ?>