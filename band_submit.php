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
$bandName = $_POST['bandName'];
$bandNameKana = $_POST['bandNameKana'];
$preferredDate = $_POST['preferredDate'];
$performanceTime = $_POST['performanceTime'];
$representativeName = $_POST['representativeName'];
$lineId = $_POST['lineId'];
$organization = $_POST['organization'] ?? "";

// 1. bandsテーブルに登録
$stmt = $conn->prepare("INSERT INTO bands (name, kana, representative_name, line_id, organization, performance_time) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $bandName, $bandNameKana, $representativeName, $lineId, $organization, $performanceTime);
$stmt->execute();
$bandId = $stmt->insert_id;
$stmt->close();

// 2. event_datesからイベントID取得、なければ作成
$stmt = $conn->prepare("SELECT id FROM event_dates WHERE event_date = ?");
$stmt->bind_param("s", $preferredDate);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $eventId = $row['id'];
} else {
    $stmt2 = $conn->prepare("INSERT INTO event_dates (event_date, location) VALUES (?, '')");
    $stmt2->bind_param("s", $preferredDate);
    $stmt2->execute();
    $eventId = $stmt2->insert_id;
    $stmt2->close();
}
$stmt->close();

// 3. 中間テーブルに出演情報を登録
$stmt = $conn->prepare("INSERT INTO band_event_entries (band_id, event_id) VALUES (?, ?)");
$stmt->bind_param("ii", $bandId, $eventId);
$stmt->execute();
$stmt->close();

// 4. メンバー情報をmembersテーブルに登録
for ($i = 1; $i <= 7; $i++) {
    $memberKey = 'member' . $i;
    if (!empty($_POST[$memberKey])) {
        $memberName = $_POST[$memberKey];
        $stmt = $conn->prepare("INSERT INTO members (band_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $bandId, $memberName);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
echo "バンド申し込みが完了しました。";
?>
