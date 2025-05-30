<?php
// DB接続
$host = 'localhost';
$user = 'ユーザー名';
$password = 'パスワード';
$dbname = 'xs980818_noralive';  // 実際のDB名に合わせてください
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

// フォームデータ取得
$email = $_POST['audienceEmail'];
$name = $_POST['audienceName'];
$kana = $_POST['audienceNameKana'];
$eventDate = $_POST['eventDate'];
$targetBand = $_POST['targetBand'];
$ticketCount = $_POST['ticketCount'];

// audiencesテーブルに登録
$stmt = $conn->prepare("INSERT INTO audiences (email, name, kana, event_date, target_band, ticket_count) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $email, $name, $kana, $eventDate, $targetBand, $ticketCount);
$stmt->execute();
$stmt->close();

$conn->close();
echo "観客申し込みが完了しました。";
?>
