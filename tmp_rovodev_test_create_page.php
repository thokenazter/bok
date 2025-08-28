<?php
// Test create page untuk melihat apakah JavaScript berfungsi
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Village;

echo "=== TEST CREATE PAGE ===\n";

// Test villages data yang akan dikirim ke view
$villages = Village::orderBy('nama')->get();
echo "Villages data for view:\n";
echo "Count: " . $villages->count() . "\n";

if ($villages->count() > 0) {
    echo "Sample data structure:\n";
    $sample = $villages->first();
    echo "- ID: " . $sample->id . "\n";
    echo "- Nama: " . $sample->nama . "\n";
    echo "- Kepala Desa: " . ($sample->kepala_desa ?? 'NULL') . "\n";
    echo "- Kecamatan: " . ($sample->kecamatan ?? 'NULL') . "\n";
    
    // Test JSON encoding (seperti yang dilakukan di Blade)
    $jsonData = json_encode($villages);
    if ($jsonData) {
        echo "✓ JSON encoding successful\n";
        echo "JSON length: " . strlen($jsonData) . " characters\n";
    } else {
        echo "✗ JSON encoding failed\n";
    }
} else {
    echo "✗ No villages data found\n";
}

echo "\n=== END TEST ===\n";