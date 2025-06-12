<?php
try {
    $host = 'localhost';
    $dbname = 'xs980818_noralive';
    $username = 'xs980818_yasu';
    $password = 'pokopixgvp';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    $sql = "SELECT *
    FROM bands
    LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
    LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id;";
    
    $params = [];


    if (isset($_GET['sort'])) {
        if ($_GET['sort'] == "ライブ日昇順") {
            $sql = "SELECT *
            FROM bands
            LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
            LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id ORDER BY event_date DESC;";

        } else {
            $sql = "SELECT *
            FROM bands
            LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
            LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id ;";

        }
    }

    if (isset($_GET["name_search"])) {
        $sql = "SELECT *
        FROM bands
        LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
        LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id WHERE name = :name;";
        $params = [':name' => $_GET['name_search']];

    }




    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    // $stmt
    $bands = $stmt->fetchAll(PDO::FETCH_ASSOC); // データを配列に保持
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
</head>

<body>
    <!-- ソートフォーム -->
    <form action="" method="get">
        <label>並び替えの順番を選んでください</label><br>
        <select name="sort">
            <option value="ライブ日昇順">ライブ日昇順</option>
            <option value="申込日昇順">申込日昇順</option>
            <option value="バンド名昇順">バンド名昇順</option>
        </select>
        <button type="submit" class="submit-btn">申し込む</button>
    </form>

    <!-- 検索フォーム -->
    <form action="" method="get">
        <label for="name_search">検索するバンド名を入力してください:</label><br>
        <input type="text" id="name" name="name_search"><br><br>
        <input type="submit" value="送信">
    </form>

    <h1>バンド一覧</h1>
    <table border="1">
        <tr>
            <th>申込日</th>
            <th>ライブ日</th>
            <th>バンド名</th>
            <th>所属団体</th>
        </tr>
        <?php foreach ($bands as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['time']); ?></t>
                <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['organization']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
    if (isset($_GET['sort'])) {
        echo $_GET['sort'];

    }

    ?>
</body>

</html>