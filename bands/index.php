<?php
$host = 'localhost';
$dbname = 'xs980818_noralive';
$username = 'xs980818_yasu';
$password = 'pokopixgvp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $sql = "SELECT * FROM bands ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $bands = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>バンド一覧</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
<h1>バンド一覧</h1>
<table>
    <tr>
        <th>ID</th>
        <th>バンド名</th>
        <th>よみ</th>
        <th>所属団体</th>
        <th>登録日</th>
    </tr>
    <?php foreach ($bands as $band): ?>
    <tr>
        <td><?= htmlspecialchars($band['id']) ?></td>
        <td><a href="show.php?id=<?= urlencode($band['id']) ?>"><?= htmlspecialchars($band['name']) ?></a></td>
        <td><?= htmlspecialchars($band['kana']) ?></td>
        <td><?= htmlspecialchars($band['organization']) ?></td>
        <td><?= htmlspecialchars($band['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
