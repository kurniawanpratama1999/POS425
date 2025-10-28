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
            $stmtOrders = $this->connect->prepare($queryOrders) ?: throw new \Exception("Gagal Membuat statement");
            $changeAmount = 0;
            $stmtOrders->bind_param('siii', $data['code'], $data['total_amount'], $data['total_amount'], $changeAmount);
            $stmtOrders->execute();
            if ($stmtOrders->affected_rows <= 0) {
                echo json_encode(["success" => false, "message" => "Gagal Melakukan input ke orders"]);
                exit;
            }

            $orderID = $stmtOrders->insert_id;

            foreach ($data['items'] as $items) {
                $queryOrderDetails = "INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)";
                $stmtOrderDetails = $this->connect->prepare($queryOrderDetails) ?: throw new \Exception("Gagal Membuat statement");
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

    private static function renderTableProducts($resultGetProducts)
    {
        ob_start() ?>
        <div class="basis-3/5 p-3 bg-slate-50">
            <section id="navigation" class="flex flex-row gap-3 items-center justify-between font-mono">
                <label for="search" class="flex flex-col rounded bg-slate-200 p-2 text-sm">
                    <input type="text" name="search" placeholder="Search Product" class="p-1 w-[300px]"
                        oninput="handleFilteringProducts()">
                </label>

                <?php if (count($resultGetProducts) > 0): ?>
                    <?php $mappingOnlyGetCategory = array_map(fn($v) => $v['category_name'], $resultGetProducts); ?>
                    <label for="category" class="flex flex-col rounded bg-slate-200 p-2 text-sm">
                        <select name="category" id="category" class="p-1" onchange="handleCategoriesSelected()">
                            <option value="all" selected>All</option>
                            <?php foreach (array_unique($mappingOnlyGetCategory) as $category): ?>
                                <option value="<?= $category ?>"><?= $category ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                <?php endif ?>
            </section>

            <section class="rounded mt-2 overflow-x-hidden">
                <table id="product-collection">
                    <thead class="bg-slate-200">
                        <tr>
                            <th class="text-left">id</th>
                            <th class="text-left">name</th>
                            <th class="text-right">price</th>
                            <th class="text-right">stock</th>
                        </tr>
                    </thead>
                    <tbody id="product-list"></tbody>
                </table>
            </section>
        </div>
        <?php return ob_get_clean();
    }
    private static function renderTransactionCollection()
    {
        ob_start(); ?>
        <section class="bg-slate-100 border-l border-slate-300 basis-2/5 grid grid-cols-1 grid-rows-[1fr_auto_auto]">
            <!-- DEAL TRANSACTION -->
            <div class="p-2 bg-black/3">
                <p class="text-sm font-mono">ORD73532035</p>
                <table id="trx-collection">
                    <thead>
                        <tr>
                            <th class='text-left w-full'>Name</th>
                            <th class="text-center">qty</th>
                            <th class="text-right">price</th>
                            <th class="text-right">total</th>
                        </tr>
                    </thead>

                    <!-- JAVASCRIPT ADD PRODUCT -->
                    <tbody id="product-selected"></tbody>
                </table>
            </div>

            <!-- MANUAL INPUT -->
            <div id="manual-input" class="p-2 flex flex-row gap-3 bg-black/3">
                <label for="product-id" class="block">
                    <input type="number" name="product-id" class="bg-slate-100 p-2 block">
                </label>
                <button type="button" onclick="add-datas" class="w-full p-2 bg-slate-100">
                    Add
                </button>
            </div>

            <!-- CALC TRANSACTION -->
            <div class="p-5 text-right">
                <p id="subtotal" class="flex flex-row justify-between items-center">
                    <span>SUBTOTAL</span>
                    <!-- JAVASCRIPT CALC -->
                    <span id="subtotal-val" class="text-xl font-bold">0</span>
                </p>
                <p id="pajak" class="flex flex-row justify-between items-center">
                    <span>PAJAK</span>
                    <!-- JAVASCRIPT CALC -->
                    <span id="pajak-val" class="text-xl font-bold">0</span>
                </p>
                <p id="total" class="flex flex-row justify-between items-center">
                    <span>TOTAL</span>
                    <!-- JAVASCRIPT CALC -->
                    <span id="total-val" class="text-3xl font-bold">0</span>
                </p>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="flex flex-row gap-3 p-2">
                <!-- JAVASCRIPT CLEAR TRANSACTION -->
                <button id="clear-transaction" class="px-3 py-2 grow bg-red-400 text-white">Clear</button>
                <!-- JAVASCRIPT API FETCH POST -->
                <button id="payment-transaction" class="px-3 py-2 grow bg-blue-400 text-white">Payments</button>
            </div>
        </section>
        <?php return ob_get_clean();
    }

    public function render()
    {
        $queryGetProducts = "SELECT p.*, pc.name AS category_name FROM products AS p JOIN product_categories AS pc ON p.id = pc.id WHERE p.deleted_at IS NULL";
        $stmtGetProducts = $this->connect->query($queryGetProducts);
        $resultGetProducts = $stmtGetProducts->fetch_all(MYSQLI_ASSOC);

        ob_start(); ?>
        <div class="flex flex-row h-[calc(100dvh-4rem)] bg-slate-100 print:hidden">

            <?= $this->renderTableProducts($resultGetProducts) ?>
            <?= $this->renderTransactionCollection() ?>
        </div>
        <script>
            const products = <?= json_encode($resultGetProducts); ?>;
        </script>
        <script src="/assets/js/trx.js"></script>
        <?= Dashboard::get(ob_get_clean(), "Transaction");
    }
} ?>