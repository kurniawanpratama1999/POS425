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

        $orders = $data['orders'];
        $order_details = $data['order_details'];


        if (!$data || !is_array($data)) {
            echo json_encode(["success" => false, "message" => "Data tidak valid"]);
        }

        $this->connect->begin_transaction();

        try {
            $queryOrders = "INSERT INTO orders (code, subtotal, tax, total_amount, payment_amount, change_amount) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtOrders = $this->connect->prepare($queryOrders) ?: throw new \Exception("Gagal Membuat statement");
            $changeAmount = (int) $orders['payment'] - (int) $orders['total'];
            $data['orders']['change'] = $changeAmount;
            $stmtOrders->bind_param(
                'siiiii',
                $orders['code'],
                $orders['subtotal'],
                $orders['tax'],
                $orders['total'],
                $orders['payment'],
                $changeAmount
            );
            $stmtOrders->execute();
            if ($stmtOrders->affected_rows <= 0) {
                echo json_encode(["success" => false, "message" => "Gagal Melakukan input ke orders"]);
                exit;
            }

            $orderID = $stmtOrders->insert_id;

            foreach ($order_details as $items) {
                $queryOrderDetails = "INSERT INTO order_details (order_id, product_id, quantity, price, tax, subtotal ,total) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtOrderDetails = $this->connect->prepare($queryOrderDetails) ?: throw new \Exception("Gagal Membuat statement");

                $stmtOrderDetails->bind_param(
                    'iiiiiii',
                    $orderID,
                    $items['id'],
                    $items['qty'],
                    $items['price'],
                    $items['tax'],
                    $items['subtotal'],
                    $items['total'],
                );
                $stmtOrderDetails->execute();
                if ($stmtOrderDetails->affected_rows <= 0) {
                    echo json_encode(["success" => false, "message" => "Gagal Melakukan input ke orders"]);
                    exit;
                }
            }

            echo json_encode(["success" => true, "message" => "Data Valid", "data" => $data]);
            $this->connect->commit();
            exit;
        } catch (mysqli_sql_exception $errSql) {
            $this->connect->rollback();
            echo json_encode(["success" => false, "message" => "Error: Gagal melakukan input -> " . $errSql]);
            exit;
        }
    }

    public function print($code)
    {

        $queryorders = "SELECT * FROM orders WHERE code = ?";
        $prepareorders = $this->connect->prepare($queryorders);
        $prepareorders->bind_param('s', $code);
        $prepareorders->execute();

        $resultOrders = $prepareorders->get_result()->fetch_assoc();

        $ordersID = 1;
        if ($resultOrders) {
            $ordersID = $resultOrders['id'];
        }

        $queryOrderDetails = "SELECT od.*, p.name FROM order_details AS od JOIN products AS P ON od.product_id = p.id WHERE od.order_id = ?";
        $prepareOrderDetails = $this->connect->prepare($queryOrderDetails);
        $prepareOrderDetails->bind_param('s', $ordersID);
        $prepareOrderDetails->execute();

        $resultOrderDetails = $prepareOrderDetails->get_result()->fetch_all(MYSQLI_ASSOC);

        ob_start(); ?>
        <div class="w-[80mm] mx-auto font-mono text-xs">
            <div class="text-center border-b border-dashed pb-2 mb-2">
                <h1>TOKO KOPI TARIK</h1>
                <p>Jl. Merdeka No. 45, Jakarta</p>
                <p>Telp: 0812-3456-7890</p>
            </div>

            <!-- Info Transaksi -->
            <div class="flex justify-between mb-2">
                <p>ID: <span class="font-semibold"><?= $code ?></span></p>
                <p>Kasir: <span class="font-semibold">Andi</span></p>
            </div>

            <div class="flex justify-between border-b border-dashed pb-2 mb-2">
                <p>Tanggal: <?= $resultOrders['created_at'] ?></p>
            </div>

            <!-- Daftar Produk -->
            <table class="w-full **:bg-transparent!">
                <thead class="border-b border-black">
                    <tr class="text-left">
                        <th>Nama</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultOrderDetails as $order_detail): ?>
                        <tr>
                            <td><?= $order_detail['name'] ?></td>
                            <td class="text-center"><?= $order_detail['quantity'] ?></td>
                            <td class="text-right"><?= $order_detail['total'] ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>

            <!-- Total -->
            <div class="border-t border-dashed mt-2 pt-2">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>Rp <?= number_format($resultOrders['subtotal'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between">
                    <span>PPN (11%)</span>
                    <span>Rp <?= number_format($resultOrders['tax'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between font-bold text-base mt-1">
                    <span>Total</span>
                    <span>Rp <?= number_format($resultOrders['total_amount'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between mt-1">
                    <span>Tunai</span>
                    <span>Rp <?= number_format($resultOrders['payment_amount'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Kembali</span>
                    <span>Rp <?= number_format($resultOrders['change_amount'], 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-3 border-t border-dashed pt-2">
                <p>Terima kasih atas kunjungan Anda!</p>
                <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
                <p class="mt-3 font-bold">*** SEMOGA HARI SENIN TERUS ***</p>
            </div>
        </div>
        <script>
            window.onload(window.print())
        </script>
    <?= Dashboard::get(ob_get_clean(), "PRINT");
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
    private static function renderTransactionCollection($runningIDfromOrders)
    {
        ob_start(); ?>
        <section class="bg-slate-100 border-l border-slate-300 basis-2/5 grid grid-cols-1 grid-rows-[1fr_auto_auto]">
            <!-- DEAL TRANSACTION -->
            <div class="p-2 bg-black/3">
                <p id="runningID" class="text-sm font-mono">ID<?= str_pad($runningIDfromOrders['Auto_increment'], 5, "0", STR_PAD_LEFT) ?></p>
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
                <button onclick="btnPayment()" id="payment-transaction" class="px-3 py-2 grow bg-blue-400 text-white">Payments</button>
            </div>
        </section>
    <?php return ob_get_clean();
    }

    public function render()
    {
        $queryGetProducts = "SELECT p.*, pc.name AS category_name FROM products AS p JOIN product_categories AS pc ON p.id = pc.id WHERE p.deleted_at IS NULL";
        $stmtGetProducts = $this->connect->query($queryGetProducts);
        $resultGetProducts = $stmtGetProducts->fetch_all(MYSQLI_ASSOC);

        $queryRunningIDfromOrders = "SHOW TABLE STATUS LIKE 'orders'";
        $stmtRunningIDfromOrders = $this->connect->query($queryRunningIDfromOrders);
        $resultRunningIDfromOrders = $stmtRunningIDfromOrders->fetch_assoc();
        ob_start(); ?>
        <div class="flex flex-row h-[calc(100dvh-4rem)] bg-slate-100 print:hidden">

            <?= $this->renderTableProducts($resultGetProducts) ?>
            <?= $this->renderTransactionCollection($resultRunningIDfromOrders) ?>
        </div>
        <script>
            const products = <?= json_encode($resultGetProducts); ?>;
        </script>
        <script src="/assets/js/trx.js"></script>
<?= Dashboard::get(ob_get_clean(), "Transaction");
    }
} ?>