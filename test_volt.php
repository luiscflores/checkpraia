<?php
require '/home/pi/checkpraia/vendor/autoload.php';
$app = require_once '/home/pi/checkpraia/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$livewireManager = $app->make(\Livewire\LivewireManager::class);

try {
    $component = $livewireManager->new('pages.auth.login');
    echo 'Component: ' . get_class($component) . "\n";
    
    // Check the compiled component file
    $ref = new ReflectionClass($component);
    $fileName = $ref->getFileName();
    echo 'File: ' . $fileName . "\n";
    
    // Search for LOGIN_ERROR in the file
    $content = file_get_contents($fileName);
    if (str_contains($content, 'LOGIN_ERROR')) {
        echo 'LOGIN_ERROR found in compiled file' . "\n";
    } else {
        echo 'LOGIN_ERROR NOT found in compiled file' . "\n";
    }
    
    // Check if form property exists
    echo 'Has form property: ' . (property_exists($component, 'form') ? 'yes' : 'no') . "\n";
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    echo 'Trace: ' . $e->getTraceAsString() . "\n";
}
