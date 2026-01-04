<?php
echo $_GET['id'];
try {
    $host = 'localhost';
    $dbname = 'xs980818_noralive';
    $username = 'xs980818_yasu';
    $password = 'pokopixgvp';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    $sql = "SELECT
    event_dates.event_date AS event_date,
    event_dates.id AS event_dates_id,
    bands.name AS bands_name
    FROM bands
    LEFT JOIN band_event_entries ON bands.id = band_event_entries.band_id
    LEFT JOIN event_dates ON band_event_entries.event_id  = event_dates.id
    WHERE  event_dates.id = :id";
    $params = ["id" => $_GET['id']];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $row) {
        echo $row['event_date'];
        echo $row['bands_name'];

    }
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <title>Document</title>
</head>

<body>
    <h1><?php echo $row['event_date']; ?></h1>
    <?php
    foreach ($result as $row) {
        echo "<p>".$row['bands_name']."</p>";

    }
    ?>

</body>

</html>