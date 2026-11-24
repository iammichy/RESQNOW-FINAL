<?php
$config = require __DIR__ . '/../app/config.php';
$db = $config['db'];
$pdo = new PDO(
    "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}",
    $db['user'],
    $db['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare("UPDATE users SET api_token = :token WHERE id = 2");
$stmt->execute(['token' => $token]);
echo "API token set for admin user (id=2):" . PHP_EOL;
echo $token . PHP_EOL;
