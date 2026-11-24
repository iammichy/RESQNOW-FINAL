<?php
$base = 'http://localhost/resqnow';
$token = '74e47d25254a649238e6a3470cbffd7bd8e05f663a871150a1a9d561fa67614d';

function apiReq($url, $method, $token, $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['status' => $info['http_code'], 'body' => $resp];
}

echo "=== API Test 1: Valid fire incident (Critical) ===" . PHP_EOL;
$r = apiReq("$base/api/v1/incidents", 'POST', $token, [
    'hazard_type' => 'fire',
    'is_emergency' => true,
    'incident_address' => '456 Oak Ave',
    'incident_lat' => 14.65,
    'incident_lng' => 121.05,
    'description' => 'House fire with people trapped',
    'answers' => [
        'trapped' => 'Yes',
        'people_affected' => '6+',
        'spreading' => 'Yes',
        'hazards' => 'Yes',
        'vulnerability' => 'Yes',
    ],
]);
echo "Status: {$r['status']}" . PHP_EOL;
echo "Response: {$r['body']}" . PHP_EOL;

echo PHP_EOL . "=== API Test 2: No auth token ===" . PHP_EOL;
$ch = curl_init("$base/api/v1/incidents");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['hazard_type' => 'fire']));
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
echo "Status: {$info['http_code']} (expect 401)" . PHP_EOL;
echo "Response: $resp" . PHP_EOL;

echo PHP_EOL . "=== API Test 3: Invalid coordinates (422) ===" . PHP_EOL;
$r = apiReq("$base/api/v1/incidents", 'POST', $token, [
    'hazard_type' => 'fire',
    'incident_lat' => 200,
    'incident_lng' => 121.05,
    'answers' => [],
]);
echo "Status: {$r['status']} (expect 422)" . PHP_EOL;
echo "Response: {$r['body']}" . PHP_EOL;

echo PHP_EOL . "=== API Test 4: Invalid hazard_type (422) ===" . PHP_EOL;
$r = apiReq("$base/api/v1/incidents", 'POST', $token, [
    'hazard_type' => 'tornado',
    'answers' => [],
]);
echo "Status: {$r['status']} (expect 422)" . PHP_EOL;
echo "Response: {$r['body']}" . PHP_EOL;

echo PHP_EOL . "=== API Test 5: Missing hazard_type (422) ===" . PHP_EOL;
$r = apiReq("$base/api/v1/incidents", 'POST', $token, [
    'answers' => [],
]);
echo "Status: {$r['status']} (expect 422)" . PHP_EOL;
echo "Response: {$r['body']}" . PHP_EOL;

echo PHP_EOL . "=== DONE ===" . PHP_EOL;
