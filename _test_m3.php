<?php
/**
 * M3 end-to-end verification flow test.
 * Run: php database/_test_m3.php
 */
require __DIR__ . '/../app/config.php';
$pdo = new PDO(
    "mysql:host=127.0.0.1;port=3306;dbname=resqnow;charset=utf8mb4",
    'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$base = 'http://localhost/resqnow';
$jar = __DIR__ . '/_m3_cookie.txt';
@unlink($jar);

function req(string $method, string $url, array $post = [], string $jar = ''): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $resp];
}

function extractCsrf(string $html): ?string {
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m)) return $m[1];
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m)) return $m[1];
    return null;
}

$pass = 0; $fail = 0;
function check(string $label, bool $cond): void {
    global $pass, $fail;
    if ($cond) { $pass++; echo "  PASS: $label\n"; }
    else { $fail++; echo "  FAIL: $label\n"; }
}

echo "=== M3 Verification Flow Test ===\n\n";

// 1. Login as admin
echo "[1] Login as admin...\n";
$r = req('GET', "$base/login", [], $jar);
$csrf = extractCsrf($r['body']);
check("Got CSRF token from login page", $csrf !== null);

$r = req('POST', "$base/login", [
    'identifier' => 'admin@test.com',
    'password' => 'admin123',
    'csrf_token' => $csrf,
], $jar);
check("Login redirect (302)", $r['code'] === 302);

// 2. Verification queue
echo "\n[2] Verification queue...\n";
$r = req('GET', "$base/verification", [], $jar);
check("GET /verification returns 200", $r['code'] === 200);
check("Queue contains RQN-2026-0001", strpos($r['body'], 'RQN-2026-0001') !== false);
check("Queue shows 'pending' count badge", strpos($r['body'], 'pending') !== false);

// 3. Report detail (with action card)
echo "\n[3] Report detail + action card...\n";
$r = req('GET', "$base/reports/RQN-2026-0001", [], $jar);
check("GET /reports/RQN-2026-0001 returns 200", $r['code'] === 200);
check("Triage breakdown card present", strpos($r['body'], 'Triage Breakdown') !== false);
check("Verification Actions card present", strpos($r['body'], 'Verification Actions') !== false);
check("Verify button present", strpos($r['body'], 'Verify') !== false);
check("Reassess modal present", strpos($r['body'], 'reassessModal') !== false);
check("Reject modal present", strpos($r['body'], 'rejectModal') !== false);
check("Duplicate modal present", strpos($r['body'], 'duplicateModal') !== false);
$csrf = extractCsrf($r['body']);
check("Got CSRF token from detail page", $csrf !== null);

// 4. Reject with missing reason (should fail - status stays submitted)
echo "\n[4] Reject without reason (should fail)...\n";
$before = $pdo->query("SELECT status FROM incidents WHERE code='RQN-2026-0001'")->fetchColumn();
$r = req('POST', "$base/reports/RQN-2026-0001/reject", [
    'csrf_token' => $csrf,
    'reason' => '',
], $jar);
$after = $pdo->query("SELECT status FROM incidents WHERE code='RQN-2026-0001'")->fetchColumn();
check("Reject without reason redirects (302)", $r['code'] === 302);
check("Status unchanged (still submitted)", $before === $after && $after === 'submitted');

// 5. Verify action
echo "\n[5] Verify action...\n";
$csrf = extractCsrf(req('GET', "$base/reports/RQN-2026-0001", [], $jar)['body']);
$r = req('POST', "$base/reports/RQN-2026-0001/verify", [
    'csrf_token' => $csrf,
], $jar);
check("Verify POST redirects (302)", $r['code'] === 302);

$status = $pdo->query("SELECT status FROM incidents WHERE code='RQN-2026-0001'")->fetchColumn();
check("Status changed to 'verified'", $status === 'verified');

$hist = $pdo->query("SELECT COUNT(*) FROM incident_status_history WHERE incident_id=1 AND to_status='verified'")->fetchColumn();
check("status_history row written (verified)", (int)$hist > 0);

$verif = $pdo->query("SELECT COUNT(*) FROM verification_records WHERE incident_id=1 AND action='verified'")->fetchColumn();
check("verification_records row written (verified)", (int)$verif > 0);

$audit = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE entity_id=1 AND action='incident.verified'")->fetchColumn();
check("audit_logs row written (verified)", (int)$audit > 0);

$notif = $pdo->query("SELECT COUNT(*) FROM notifications WHERE user_id = (SELECT reported_by FROM incidents WHERE id=1)")->fetchColumn();
check("Resident notification created", (int)$notif > 0);

// 6. Reassess action (on RQN-2026-0002 which is still submitted)
echo "\n[6] Reassess action...\n";
$origLevel = $pdo->query("SELECT triage_level FROM incidents WHERE code='RQN-2026-0002'")->fetchColumn();
$csrf = extractCsrf(req('GET', "$base/reports/RQN-2026-0002", [], $jar)['body']);
$r = req('POST', "$base/reports/RQN-2026-0002/reassess", [
    'csrf_token' => $csrf,
    'triage_level' => 'moderate',
    'reason' => 'Test reassessment reason',
], $jar);
check("Reassess POST redirects (302)", $r['code'] === 302);

$newLevel = $pdo->query("SELECT triage_level FROM incidents WHERE code='RQN-2026-0002'")->fetchColumn();
check("Triage changed to 'moderate'", $newLevel === 'moderate');

$reassess = $pdo->query("SELECT COUNT(*) FROM verification_records WHERE incident_id=2 AND action='reassessed' AND new_triage_level='moderate'")->fetchColumn();
check("verification_records row written (reassessed)", (int)$reassess > 0);

$hist2 = $pdo->query("SELECT COUNT(*) FROM incident_status_history WHERE incident_id=2 AND notes LIKE '%reassessed%'")->fetchColumn();
check("status_history row written (reassess)", (int)$hist2 > 0);

$audit2 = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE entity_id=2 AND action='incident.reassessed'")->fetchColumn();
check("audit_logs row written (reassessed)", (int)$audit2 > 0);

// 7. Reject with reason on RQN-2026-0002
echo "\n[7] Reject with reason...\n";
$csrf = extractCsrf(req('GET', "$base/reports/RQN-2026-0002", [], $jar)['body']);
$r = req('POST', "$base/reports/RQN-2026-0002/reject", [
    'csrf_token' => $csrf,
    'reason' => 'Test rejection with valid reason',
], $jar);
check("Reject POST redirects (302)", $r['code'] === 302);

$status2 = $pdo->query("SELECT status FROM incidents WHERE code='RQN-2026-0002'")->fetchColumn();
check("Status changed to 'rejected'", $status2 === 'rejected');

$rej = $pdo->query("SELECT COUNT(*) FROM verification_records WHERE incident_id=2 AND action='rejected'")->fetchColumn();
check("verification_records row written (rejected)", (int)$rej > 0);

// 8. API GET endpoint
echo "\n[8] API GET /api/v1/incidents/{code}...\n";
$token = $pdo->query("SELECT api_token FROM users WHERE id=2")->fetchColumn();
$ch = curl_init("$base/api/v1/incidents/RQN-2026-0001");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"],
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$api = json_decode($resp, true);
check("API returns 200", $code === 200);
check("API returns report_code", ($api['report_code'] ?? '') === 'RQN-2026-0001');
check("API returns status_history array", isset($api['status_history']) && is_array($api['status_history']));
check("API returns answers array", isset($api['answers']) && is_array($api['answers']));
check("API returns triage_level", isset($api['triage_level']));

// 9. Verify button disabled when pending_reassessment
echo "\n[9] Verify button disabled for pending_reassessment...\n";
$pdo->exec("UPDATE incidents SET triage_level='pending_reassessment' WHERE code='RQN-2026-0002'");
$r = req('GET', "$base/reports/RQN-2026-0002", [], $jar);
check("Verify button has 'disabled' attribute", strpos($r['body'], 'disabled') !== false);
$pdo->exec("UPDATE incidents SET triage_level='moderate' WHERE code='RQN-2026-0002'");

// 10. Sidebar count badge on dashboard
echo "\n[10] Sidebar verification count badge...\n";
$r = req('GET', "$base/dashboard", [], $jar);
check("Dashboard renders", $r['code'] === 200);

echo "\n=== Results: $pass passed, $fail failed ===\n";
@unlink($jar);
exit($fail > 0 ? 1 : 0);
