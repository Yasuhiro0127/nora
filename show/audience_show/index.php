<?php
try {
    $host = 'localhost';
    $dbname = 'xs980818_noralive';
    $username = 'xs980818_yasu';
    $password = 'pokopixgvp';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    $sql = "SELECT id, email, name, kana, event_date, target_band, ticket_count FROM audiences";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $audiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>観客一覧</title>
    <style>
        .audience_table {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            th, td {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <h1>観客一覧</h1>
    <div class="audience_table">
        <table border="1">
            <tr>
                <th>ID</th>
                <th>登録日</th>
                <th>メール</th>
                <th>名前</th>
                <th>カナ</th>
                <th>日付</th>
                <th>目的のバンド</th>
                <th>枚数</th>
            </tr>
            <?php foreach ($audiences as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['time'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['kana']); ?></td>
                <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                <td><?php echo htmlspecialchars($row['target_band']); ?></td>
                <td><?php echo htmlspecialchars($row['ticket_count']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>