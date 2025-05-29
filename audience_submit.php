<?php
$host = 'localhost';
$dbname = 'noralive';
$username = 'xs980818';
$password = 'pokopixgvp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO audiences (email, name, name_kana, event_date, target_band, ticket_count)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['audienceEmail'],
        $_POST['audienceName'],
        $_POST['audienceNameKana'],
        $_POST['eventDate'],
        $_POST['targetBand'] ?? null,
        $_POST['ticketCount']
    ]);

    echo "観客申し込みが完了しました。";
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>
