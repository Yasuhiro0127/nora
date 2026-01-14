<?php
$host = '49.212.150.114';
$dbname = 'xs980818_noralive';
$username = 'xs980818_yasu';
$password = 'pokopixgvp';

$error = false;
$errorMessage = '';

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

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $error = true;
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申し込み完了 - 野良ライヴ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .result-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 3rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #4ecdc4, #6bcf7f);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            box-shadow: 0 10px 30px rgba(78, 205, 196, 0.4);
        }
        .error-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #ff6b6b, #ee5a5a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.4);
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #ff6b6b, #ffd93d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .message {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
        }
        .notice {
            background: rgba(255, 217, 61, 0.1);
            border: 1px solid rgba(255, 217, 61, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.95rem;
            color: #ffd93d;
        }
        .back-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(45deg, #ff6b6b, #ffd93d);
            color: #000;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
        }
        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 107, 107, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-card">
            <?php if (!$error): ?>
                <div class="success-icon">&#10004;</div>
                <h1>申し込み完了</h1>
                <p class="message">
                    バンド申し込みが完了しました。<br>
                    ご登録ありがとうございます。
                </p>
                <div class="notice">
                    申し込み完了メールが届きます。<br>
                    届かない場合は迷惑メールフォルダをご確認ください。
                </div>
            <?php else: ?>
                <div class="error-icon">&#10006;</div>
                <h1>エラーが発生しました</h1>
                <p class="message">
                    申し訳ございません。<br>
                    申し込み処理中にエラーが発生しました。
                </p>
            <?php endif; ?>
            <a href="index.html" class="back-btn">トップページに戻る</a>
        </div>
    </div>
</body>
</html>
