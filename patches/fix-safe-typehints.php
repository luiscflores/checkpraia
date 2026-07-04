<?php
/**
 * Patches thecodingmachine/safe PHP 8.x type hint warnings.
 * resource → mixed, integer → int in function signatures.
 */

$patches = [
    'vendor/thecodingmachine/safe/generated/sockets.php' => [
        ['function socket_wsaprotocol_info_export(resource  $socket', 'function socket_wsaprotocol_info_export(mixed  $socket'],
    ],
    'vendor/thecodingmachine/safe/generated/swoole.php' => [
        ['integer $offset = null', 'int $offset = null'],
    ],
];

$base = __DIR__ . '/..';

foreach ($patches as $relative => $filePatches) {
    $file = $base . '/' . $relative;
    if (!file_exists($file)) {
        echo "  SKIP: $file not found\n";
        continue;
    }
    $content = file_get_contents($file);
    $original = $content;
    foreach ($filePatches as [$search, $replace]) {
        if (str_contains($content, $search) && !str_contains($content, $replace)) {
            $content = str_replace($search, $replace, $content);
            echo "  PATCHED: $search → $replace\n";
        }
    }
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "  SAVED: $file\n";
    } else {
        echo "  OK: $file already patched\n";
    }
}

echo "Done.\n";
