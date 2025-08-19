<?php
$host = 'localhost';
$dbname = 'xs980818_noralive';
$username = 'xs980818_yasu';
$password = 'pokopixgvp';

$id = $_GET['id'] ?? null;
if ($id === null) {
    echo 'IDが指定されていません';
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $stmt = $pdo->prepare("SELECT * FROM bands WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $band = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$band) {
        echo 'バンドが見つかりません';
        exit;
    }
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>バンド詳細</title>
</head>
<body>
<h1>バンド詳細</h1>
<ul>
    <li>ID: <?= htmlspecialchars($band['id']) ?></li>
    <li>バンド名: <?= htmlspecialchars($band['name']) ?></li>
    <li>よみ: <?= htmlspecialchars($band['kana']) ?></li>
    <li>所属団体: <?= htmlspecialchars($band['organization']) ?></li>
    <li>登録日: <?= htmlspecialchars($band['created_at']) ?></li>
</ul>
<p><a href="index.php">一覧に戻る</a></p>
</body>
</html>
