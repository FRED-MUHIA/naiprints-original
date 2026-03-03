<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$header = App\Models\GlobalSection::where('key', 'header')->first();
if (!$header) {
    echo "no header" . PHP_EOL;
    exit(0);
}
$content = $header->content ?? [];
$menu = $content['menu'] ?? [];
$filtered = array_values(array_filter($menu, function ($item) {
    $label = strtolower(trim($item['label'] ?? ''));
    $url = trim($item['url'] ?? '');
    return $label !== 'designs' && $url !== '/designs';
}));
$content['menu'] = $filtered;
if (isset($content['designs_menu_added'])) {
    unset($content['designs_menu_added']);
}
$header->update(['content' => $content]);

echo "updated" . PHP_EOL;
