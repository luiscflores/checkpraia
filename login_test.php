<?php
$html = file_get_contents('/tmp/login.html');
preg_match('/name="_token" value="([^"]+)"/', $html, $m);
$token = $m[1];
echo "Token: $token\n";

preg_match('/wire:snapshot="([^"]+)"/', $html, $m2);
$snapshot = str_replace('&quot;', '"', $m2[1]);
echo "Snapshot: " . substr($snapshot, 0, 80) . "...\n";

$data = json_decode($snapshot, true);
$data['data']['form'][0]['email'] = 'admin@checkpraia.pt';
$data['data']['form'][0]['password'] = 'password';
$newSnapshot = json_encode($data);

$payload = json_encode([
    'components' => [[
        'snapshot' => $newSnapshot,
        'calls' => [['path' => '', 'method' => 'login', 'params' => []]]
    ]]
]);

$ch = curl_init('http://localhost/livewire/update');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: ' . $token,
    'X-Livewire: true',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
]);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cjar');
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cjar2');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP: $httpCode\n";
echo "Response:\n" . $response . "\n";
