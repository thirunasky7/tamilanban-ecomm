<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $html = view('store.cart', ['items' => [], 'subtotal' => 0])->render();
    echo 'OK '.strlen($html).PHP_EOL;
} catch (Throwable $e) {
    echo 'ERR: '.$e->getMessage().PHP_EOL;
    echo $e->getFile().':'.$e->getLine().PHP_EOL;
}
