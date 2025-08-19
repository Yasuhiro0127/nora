<?php
$host = 'localhost';
$dbname = 'xs980818_noralive';
$username = 'xs980818_yasu';
$password = 'pokopixgvp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    $bandName = trim($_POST['bandName'] ?? '');
    $bandNameKana = trim($_POST['bandNameKana'] ?? '');
    $organization = trim($_POST['organization'] ?? '');

    if ($bandName === '' || $bandNameKana === '') {
        throw new Exception('必須項目が未入力です。');
    }

    $stmt = $pdo->prepare("INSERT INTO bands (name, kana, organization) VALUES (?, ?, ?)");
    $stmt->execute([$bandName, $bandNameKana, $organization]);

    header('Location: /bands');
    exit;
} catch (Exception $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>

