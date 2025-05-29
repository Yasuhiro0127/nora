<?php
// DB情報
$host = 'localhost';
$dbname = 'xs980818_noralive';
$username = 'xs980818';
$password = 'pokopixgvp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // バンド情報の挿入
    $stmt = $pdo->prepare("INSERT INTO bands (band_name, band_name_kana, preferred_date, performance_time, representative_name, line_id, organization)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['bandName'],
        $_POST['bandNameKana'],
        $_POST['preferredDate'],
        $_POST['performanceTime'],
        $_POST['representativeName'],
        $_POST['lineId'],
        $_POST['organization'] ?? null
    ]);

    // 挿入されたband_idを取得
    $bandId = $pdo->lastInsertId();

    // メンバーの登録
    for ($i = 1; $i <= 7; $i++) {
        $memberName = $_POST["member$i"] ?? null;
        if (!empty($memberName)) {
            $stmt = $pdo->prepare("INSERT INTO members (band_id, name) VALUES (?, ?)");
            $stmt->execute([$bandId, $memberName]);
        }
    }

    echo "申し込みが完了しました。";
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>
