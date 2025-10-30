<?php

namespace App\Pages\Layouts;

use App\Utils\Message;

class Dashboard
{
    public static function get($content, $title = "POS425"): string
    {
        $isBold = fn($path) => str_contains($_SERVER['REQUEST_URI'], $path) ? "font-bold" : "";
        $message = Message::get();
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
            <header class="bg-blue-400 print:hidden">
                <nav class="p-2">
                    <ul class="text-white flex flex-row gap-7 h-12 items-center">
                        <li class="text-xl font-bold">
                            <a href="/dashboard/users">POS425</a>
                        </li>

                        <li class="menu ml-auto <?= $isBold('/user-roles') ?>">
                            <a href="/dashboard/user-roles">ROLES</a>
                        </li>

                        <li class="menu <?= $isBold('/product-categories') ?>">
                            <a href="/dashboard/product-categories">CATEGORIES</a>
                        </li>

                        <li class="menu <?= $isBold('/users') ?>">
                            <a href="/dashboard/users">USERS</a>
                        </li>

                        <li class="menu <?= $isBold('/products') ?>">
                            <a href="/dashboard/products">PRODUCTS</a>
                        </li>

                        <li class="menu mr-auto <?= $isBold('/transactions') ?>"><a
                                href="/dashboard/transactions">TRANSACTIONS</a>
                        </li>

                        <li>
                            <form action="/logout" method="POST">
                                <input type="hidden" name="_METHOD_" value="DELETE">
                                <button type="submit" class="text-xl font-bold">LOGOUT</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </header>
            <?= $message ?? "" ?>
            <?= $content ?>
            <script>
                const messageElement = document.getElementById('message');
                if (messageElement) {
                    setTimeout(() => {
                        messageElement.remove()
                    }, 1000);
                }

                const collectingMenu = document.querySelectorAll("nav ul li.menu a")

                let cols = 0;
                const curLocation = window.location.pathname
                collectingMenu.forEach((cm, k) => {
                    const href = cm.getAttribute('href')
                    if (curLocation === href) {
                        cols = k
                    }
                })

                const minMenu = 0;
                const maxMenu = collectingMenu.length - 1;

                document.addEventListener("keydown", (e) => {
                    if (e.ctrlKey) {
                        if (e.key === 'ArrowRight') {
                            cols++
                            if (cols > maxMenu) {
                                cols = minMenu
                            }

                            window.location.href = collectingMenu[cols].getAttribute('href')
                        } else if (e.key === 'ArrowLeft') {
                            cols--
                            if (cols < minMenu) {
                                cols = maxMenu
                            }

                            window.location.href = collectingMenu[cols].getAttribute('href')
                        }
                    }
                })
            </script>
        </body>

        </html>
        <?php return ob_get_clean();
    }
} ?>