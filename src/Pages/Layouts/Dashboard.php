<?php

namespace App\Pages\Layouts;

class Dashboard
{
    public static function get($content, $title = "POS425"): string
    {
        $isBold = fn($path) => str_contains($_SERVER['PATH_INFO'], $path) ? "font-bold" : "";

        ob_start(); ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashboard | <?= $title ?></title>
            <link rel="stylesheet" href="/assets/css/output.css">
        </head>

        <body>
            <header class="bg-blue-400">
                <nav class="p-2">
                    <ul class="text-white flex flex-row gap-7 h-12 items-center">
                        <li class="text-xl font-bold"><a href="/dashboard/users">POS425</a></li>
                        <li class="ml-auto <?= $isBold('/users') ?>"><a href="/dashboard/users">USERS</a></li>
                        <li class="<?= $isBold('/user-roles') ?>"><a href="/dashboard/user-roles">ROLES</a></li>
                        <li class="<?= $isBold('/products') ?>"><a href="/dashboard/products">PRODUCTS</a></li>
                        <li class="<?= $isBold('/product-categories') ?>"><a href="/dashboard/product-categories">CATEGORIES</a>
                        </li>
                        <li class="mr-auto <?= $isBold('/transactions') ?>"><a href="/dashboard/transactions">TRANSACTIONS</a>
                        </li>
                        <li><a href="/dashboard/transactions">LOGOUT</a></li>
                    </ul>
                </nav>
            </header>
            <?= $content ?>
        </body>

        </html>
        <?php return ob_get_clean();
    }
} ?>