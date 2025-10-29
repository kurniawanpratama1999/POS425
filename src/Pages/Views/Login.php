<?php

namespace App\Pages\Views;

use App\Utils\Message;

class Login
{
    public function render()
    {
        $message = Message::get();
        ob_start(); ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login</title>
            <link rel="stylesheet" href="/assets/css/output.css">
        </head>

        <body>
            <div class="h-dvh bg-slate-100 flex items-center justify-center gap-2 flex-col">
                <div class="space-y-2">
                    <?php if ($message): ?>
                        <?= $message["success"] ? $message['message'] : $message['message'] ?>
                    <?php endif ?>
                    <h1 class="text-2xl font-bold text-center font-serif flex items-center gap-1 flex-col">
                        <span>SELAMAT DATANG</span>
                        <span>DI POS425</span>
                    </h1>
                    <form action="/login" method="POST" class="flex flex-col gap-2 p-3 rounded shadow w-[350px]">
                        <label for="email" class="border rounded border-slate-300 flex flex-col p-2">
                            <span class="text-sm">Email <small class="text-xs text-red-700">*</small></span>
                            <input type="text" name="email" placeholder="ex: name@mail.com" class="border-0 outline-0 ">
                        </label>

                        <label for="password" class="border rounded border-slate-300 flex flex-col p-2">
                            <span class="text-sm">Password <small class="text-xs text-red-700">*</small></span>
                            <input type="text" name="password" placeholder="******" class="border-0 outline-0 ">
                        </label>

                        <button type="submit" class="text-xl text-white bg-emerald-400 p-3 rounded">LOGIN</button>
                    </form>
                    <a href="/dokumentasi" class="text-center block text-blue-600">Baca cara pemakaian</a>
                </div>
            </div>
        </body>

        </html>
<?= ob_get_clean();
    }
}
