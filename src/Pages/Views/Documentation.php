<?php

namespace App\Pages\Views;

class Documentation
{
    public function render()
    {
        ob_start() ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dokumentasi</title>
            <link rel="stylesheet" href="/assets/css/output.css">
        </head>

        <body>
            <p>Kalo mau login emailnya admin@gmail.com</p>
            <p>Password: admin1234</p>

            <p>kalo ada permasalahan dengan mysql, pastikan mysql udah nyala, buka XAMPP dan start mysql</p>
        </body>

        </html>
<?= ob_get_clean();
    }
}
