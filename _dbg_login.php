<?php
$jar = __DIR__ . '/_dbg_cookie.txt';
@unlink($jar);
$ch = curl_init('http://localhost/resqnow/login');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HEADER=>true, CURLOPT_COOKIEJAR=>$jar, CURLOPT_COOKIEFILE=>$jar]);
$r = curl_exec($ch);
curl_close($ch);
preg_match('/name="csrf_token" value="([^"]+)"/', $r, $m);
$csrf = $m[1] ?? 'NONE';
echo "CSRF: $csrf\n";

$ch = curl_init('http://localhost/resqnow/login');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HEADER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>http_build_query(['email'=>'admin@test.com','password'=>'admin123','csrf_token'=>$csrf]), CURLOPT_COOKIEJAR=>$jar, CURLOPT_COOKIEFILE=>$jar]);
$r = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "LOGIN STATUS: $code\n";
echo "--- first 600 chars ---\n";
echo substr($r, 0, 600) . "\n";
