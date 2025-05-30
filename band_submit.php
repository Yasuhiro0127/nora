<?php
$host = 'localhost';
$dbname = 'xs980818_noralive';
$username = 'xs980818_yasu';
$password = 'pokopixgvp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // POSTデータ取得
    $bandName = $_POST['bandName'];
    $bandNameKana = $_POST['bandNameKana'];
    $preferredDate = $_POST['preferredDate'];
    $performanceTime = $_POST['performanceTime'];
    $representativeName = $_POST['representativeName'];
    $lineId = $_POST['lineId'];
    $organization = $_POST['organization'];

    $pdo->beginTransaction();

    // 1. 同名バンドが存在するかチェック
    $stmt = $pdo->prepare("SELECT id FROM bands WHERE name = ? AND kana = ? AND organization = ?");
    $stmt->execute([$bandName, $bandNameKana, $organization]);
    $bandId = $stmt->fetchColumn();

    if (!$bandId) {
        // 新規バンド登録
        $stmt = $pdo->prepare("INSERT INTO bands (name, kana, organization) VALUES (?, ?, ?)");
        $stmt->execute([$bandName, $bandNameKana, $organization]);
        $bandId = $pdo->lastInsertId();
    }

    // 2. イベント日が存在するか確認（なければ追加）
    $stmt = $pdo->prepare("SELECT id FROM event_dates WHERE event_date = ?");
    $stmt->execute([$preferredDate]);
    $eventId = $stmt->fetchColumn();

    if (!$eventId) {
        $stmt = $pdo->prepare("INSERT INTO event_dates (event_date) VALUES (?)");
        $stmt->execute([$preferredDate]);
        $eventId = $pdo->lastInsertId();
    }

    // 3. 中間テーブルに登録（イベントごとの演奏時間・代表者情報含む）
    $stmt = $pdo->prepare("
        INSERT INTO band_event_entries 
        (band_id, event_id, performance_time, representative_name, line_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$bandId, $eventId, $performanceTime, $representativeName, $lineId]);

    // 4. メンバー登録（最大7人）
    for ($i = 1; $i <= 7; $i++) {
        $member = $_POST["member$i"] ?? '';
        if (!empty($member)) {
            $stmt = $pdo->prepare("INSERT INTO members (band_id, name) VALUES (?, ?)");
            $stmt->execute([$bandId, $member]);
        }
    }

    $pdo->commit();
    echo "🎉 登録完了しました！";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "エラー: " . $e->getMessage();
}
?>
