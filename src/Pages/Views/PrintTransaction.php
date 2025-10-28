<?php

namespace App\Pages\Views;

use App\Pages\Layouts\Dashboard;

class PrintTransaction
{
    public function render($order_id)
    {
        ob_start(); ?>
        <div class="max-w-[80mm] mx-auto font-mono">
            <div style="border-style: dashed;" class="text-center py-2 border-y border-black">
                <h2 class="text-center">-- POS425 --</h2>
                <p class="text-center">Jl. Land of dawn, mobile legend, moonton</p>
            </div>
            <div style="border-style: dashed;" class="text-center py-2 border-b border-black">
                <?= $order_id ?>
            </div>
        </div>
        <script>
            window.onload(window.print())
        </script>
<?= Dashboard::get(ob_get_clean());
    }
} ?>