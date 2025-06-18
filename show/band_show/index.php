<?php
try {
    $host = 'localhost';
    $dbname = 'xs980818_noralive';
    $username = 'xs980818_yasu';
    $password = 'pokopixgvp';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    $sql = "SELECT 
    bands.time AS time,
    bands.name AS name,
    bands.organization AS organization,
    event_dates.event_date AS event_date,
    band_event_entries.id AS  event_entries_id,
    event_dates.id AS event_dates_id,
    band_event_entries.representative_name AS representative_name,
    band_event_entries.line_id AS line_id,
    band_event_entries.performance_time AS performance_time


    FROM bands
    LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
    LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id;";

    $params = [];


    if (isset($_GET['sort'])) {
        if ($_GET['sort'] == "ライブ日昇順") {
            $sql = "SELECT 
            bands.time AS time,
            bands.name AS name,
            bands.organization AS organization,
            event_dates.event_date AS event_date,
            band_event_entries.id AS  event_entries_id,
            event_dates.id AS event_dates_id,
            band_event_entries.representative_name AS representative_name,
            band_event_entries.line_id AS line_id,
            band_event_entries.performance_time AS performance_time
            FROM bands
            LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
            LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id ORDER BY event_date DESC;";

        } else {
            $sql = "SELECT 
            bands.time AS time,
            bands.name AS name,
            bands.organization AS organization,
            event_dates.event_date AS event_date,
            band_event_entries.id AS  event_entries_id,
            event_dates.id AS event_dates_id,
            band_event_entries.representative_name AS representative_name,
            band_event_entries.line_id AS line_id,
            band_event_entries.performance_time AS performance_time
            FROM bands
            LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
            LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id ;";

        }
    }

    if (isset($_GET["name_search"])) {
        $sql = "SELECT 
        bands.time AS time,
        bands.name AS name,
        bands.organization AS organization,
        event_dates.event_date AS event_date,
        band_event_entries.id AS  event_entries_id,
        event_dates.id AS event_dates_id,
        band_event_entries.representative_name AS representative_name,
        band_event_entries.line_id AS line_id,
        band_event_entries.performance_time AS performance_time
        FROM bands
        LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
        LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id WHERE name = :name;";
        $params = [':name' => $_GET['name_search']];

    }

    if (isset($_GET['date_search'])) {
        $sql = "SELECT 
        bands.time AS time,
        bands.name AS name,
        bands.organization AS organization,
        event_dates.event_date AS event_date,
        band_event_entries.id AS  event_entries_id,
        event_dates.id AS event_dates_id,
        band_event_entries.representative_name AS representative_name,
        band_event_entries.line_id AS line_id,
        band_event_entries.performance_time AS performance_time
        FROM bands
        LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
        LEFT JOIN event_dates ON band_event_entries.event_id = event_dates.id WHERE event_dates.event_date = :event_date;";
        $params = [':event_date' => $_GET['date_search']];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バンド一覧</title>
    <style>
        .band_table {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }

        @media (max-width: 600px) {

            th,
            td {
                font-size: 12px;
            }
        }
    </style>
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

    <form action="" method="get">
        <label for="date_search">検索する日付を入力してください:</label><br>
        <input type="date" id="date_search" name="date_search"><br><br>
        <input type="submit" value="送信">
    </form>

    <h1>バンド一覧</h1>
    <div class="band_table">
        <table border="1">
            <tr>
                <th>申込日</th>
                <th>ライブ日</th>
                <th>バンド名</th>
                <th>所属団体</th>
                <th>申し込み</th>
                <th>LINE ID</th>
                <th>パフォーマンスタイム</th>

            </tr>
            <?php foreach ($bands as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['time']); ?></td>
                    <td><a
                            href="https://noralive.net/show/event_detail/index.php?id=<?php echo htmlspecialchars($row['event_dates_id']); ?>"><?php echo htmlspecialchars($row['event_date']); ?></a>
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['organization']); ?></td>
                    <td><?php echo htmlspecialchars($row['representative_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['line_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['performance_time']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- <?php
    // if (isset($_GET['sort'])) {
    //     echo $_GET['sort'];
    
    // }
    
    ?> -->
</body>

</html>