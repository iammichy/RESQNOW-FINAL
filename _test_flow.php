<?php
$base = 'http://localhost/resqnow';
$cookieFile = tempnam(sys_get_temp_dir(), 'resqnow_cookie_');

function req($url, $method = 'GET', $post = null, $cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    $headerSize = $info['header_size'];
    $headers = substr($resp, 0, $headerSize);
    $body = substr($resp, $headerSize);
    curl_close($ch);
    return ['status' => $info['http_code'], 'body' => $body, 'headers' => $headers];
}

function extractCsrf($body) {
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $body, $m)) return $m[1];
    return null;
}

echo "=== 1. Login ===" . PHP_EOL;
$r = req("$base/login", 'GET', null, $cookieFile);
$csrf = extractCsrf($r['body']);
$r = req("$base/login", 'POST', ['csrf_token' => $csrf, 'identifier' => 'admin@test.com', 'password' => 'admin123'], $cookieFile);
echo "Login: status={$r['status']} (302 = success)" . PHP_EOL;

echo PHP_EOL . "=== 2. GET /reports/new (step 1) ===" . PHP_EOL;
$r = req("$base/reports/new", 'GET', null, $cookieFile);
$csrf = extractCsrf($r['body']);
echo "Step1 GET: status={$r['status']}, csrf=" . ($csrf ? 'yes' : 'no') . PHP_EOL;

echo PHP_EOL . "=== 3. POST step 1 (fire) ===" . PHP_EOL;
$r = req("$base/reports/new", 'POST', ['csrf_token' => $csrf, 'step' => '1', 'hazard_type' => 'fire', 'is_emergency' => '1'], $cookieFile);
echo "Step1 POST: status={$r['status']}" . PHP_EOL;
if (preg_match('/Location:\s*(.+)/i', $r['headers'], $m)) echo "  -> " . trim($m[1]) . PHP_EOL;

echo PHP_EOL . "=== 4. POST step 2 (answers) ===" . PHP_EOL;
$r = req("$base/reports/new?step=2", 'GET', null, $cookieFile);
$csrf = extractCsrf($r['body']);
$r = req("$base/reports/new", 'POST', [
    'csrf_token' => $csrf, 'step' => '2',
    'answers[trapped]' => 'Yes',
    'answers[people_affected]' => '6+',
    'answers[spreading]' => 'Yes',
    'answers[hazards]' => 'Yes',
    'answers[vulnerability]' => 'Yes',
], $cookieFile);
echo "Step2 POST: status={$r['status']}" . PHP_EOL;
if (preg_match('/Location:\s*(.+)/i', $r['headers'], $m)) echo "  -> " . trim($m[1]) . PHP_EOL;

echo PHP_EOL . "=== 5. POST step 3 (location) ===" . PHP_EOL;
$r = req("$base/reports/new?step=3", 'GET', null, $cookieFile);
$csrf = extractCsrf($r['body']);
$r = req("$base/reports/new", 'POST', [
    'csrf_token' => $csrf, 'step' => '3',
    'incident_address' => '123 Main St',
    'incident_lat' => '14.6000000',
    'incident_lng' => '121.0000000',
    'description' => 'Test fire',
], $cookieFile);
echo "Step3 POST: status={$r['status']}" . PHP_EOL;
if (preg_match('/Location:\s*(.+)/i', $r['headers'], $m)) echo "  -> " . trim($m[1]) . PHP_EOL;

echo PHP_EOL . "=== 6. POST step 4 (final submit) ===" . PHP_EOL;
$r = req("$base/reports/new?step=4", 'GET', null, $cookieFile);
$csrf = extractCsrf($r['body']);
$r = req("$base/reports/new", 'POST', ['csrf_token' => $csrf, 'step' => '4'], $cookieFile);
echo "Step4 POST: status={$r['status']}" . PHP_EOL;
$detailUrl = null;
if (preg_match('/Location:\s*(.+)/i', $r['headers'], $m)) {
    $detailUrl = trim($m[1]);
    echo "  -> " . $detailUrl . PHP_EOL;
}

echo PHP_EOL . "=== 7. GET detail page ===" . PHP_EOL;
if ($detailUrl) {
    $fullUrl = (strpos($detailUrl, 'http') === 0) ? $detailUrl : "http://localhost" . $detailUrl;
    $r = req($fullUrl, 'GET', null, $cookieFile);
    echo "Detail: status={$r['status']}" . PHP_EOL;
    echo "  Triage Breakdown card: " . (strpos($r['body'], 'Triage Breakdown') !== false ? 'FOUND' : 'MISSING') . PHP_EOL;
    echo "  Report code: " . (preg_match('/RQN-\d{4}-\d{4}/', $r['body']) ? 'FOUND' : 'MISSING') . PHP_EOL;
    echo "  Critical badge: " . (strpos($r['body'], 'critical') !== false ? 'FOUND' : 'MISSING') . PHP_EOL;
    echo "  Score 16: " . (strpos($r['body'], '>16<') !== false ? 'FOUND' : 'MISSING') . PHP_EOL;
}

echo PHP_EOL . "=== 8. GET /reports (list) ===" . PHP_EOL;
$r = req("$base/reports", 'GET', null, $cookieFile);
echo "List: status={$r['status']}" . PHP_EOL;
echo "  All Reports title: " . (strpos($r['body'], 'All Reports') !== false ? 'FOUND' : 'MISSING') . PHP_EOL;
echo "  Report in list: " . (preg_match('/RQN-\d{4}-\d{4}/', $r['body']) ? 'FOUND' : 'MISSING') . PHP_EOL;

@unlink($cookieFile);
echo PHP_EOL . "=== DONE ===" . PHP_EOL;
