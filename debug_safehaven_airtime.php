<?php

use App\Http\Helpers\SafeHeaven\VASHelper;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vasHelper = app(VASHelper::class);

echo "Fetching Services...\n";
$services = $vasHelper->services();
$airtimeServiceId = null;

if (isset($services['data'])) {
    foreach ($services['data'] as $service) {
        if ($service['name'] == 'Mobile Recharge' || $service['name'] == 'Airtime') {
            echo "Found Airtime Service: " . $service['name'] . " (ID: " . $service['_id'] . ")\n";
            $airtimeServiceId = $service['_id'];
            break;
        }
    }
}

if ($airtimeServiceId) {
    echo "\nFetching Categories for Service ID: $airtimeServiceId ...\n";
    $categories = $vasHelper->serviceCategories($airtimeServiceId);
    print_r($categories);
} else {
    echo "Airtime Service NOT FOUND.\n";
}
