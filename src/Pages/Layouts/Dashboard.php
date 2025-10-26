<?php
namespace App\Pages\Layouts;

class Dashboard
{
    public static function get($content, $title = "POS425"): string
    {
        ob_start(); ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashboard | <?= $title ?></title>
        </head>

        <body>
            <header>
                <nav>
                    <ul>
                        <li><a href="/dashboard/users">USERS</a></li>
                        <li><a href="/dashboard/user-roles">ROLES</a></li>
                        <li><a href="/dashboard/products">PRODUCTS</a></li>
                        <li><a href="/dashboard/product-categories">CATEGORIES</a></li>
                    </ul>
                </nav>
            </header>
            <?= $content ?>
        </body>

        </html>
        <?php return ob_get_clean();
    }
} ?>