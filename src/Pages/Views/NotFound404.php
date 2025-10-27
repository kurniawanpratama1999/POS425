<?php
namespace App\Pages\Views;

use App\Pages\Layouts\Dashboard;

class NotFound404
{
    public function render()
    {
        ob_start() ?>
        <div class="h-[calc(100dvh-4rem)] bg-slate-100 flex flex-col items-center justify-center text-2xl font-serif">
            <p>Kamu nyasar? Halaman yang kamu cari gak ada loh ya</p>
            <p>404 | Not Found</p>
        </div>
        <?= Dashboard::get(ob_get_clean(), "404 Not Found");
    }
}
?>