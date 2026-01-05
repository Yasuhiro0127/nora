<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/mailer.php';

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

$host = 'localhost';
$dbname = 'xs980818_noralive';
$username = 'xs980818_yasu';
$password = 'pokopixgvp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // POSTデータ取得
    $bandName = trim((string)($_POST['bandName'] ?? ''));
    $bandNameKana = trim((string)($_POST['bandNameKana'] ?? ''));
    $preferredDate = trim((string)($_POST['preferredDate'] ?? ''));
    $performanceTime = trim((string)($_POST['performanceTime'] ?? ''));
    $representativeName = trim((string)($_POST['representativeName'] ?? ''));
    $representativeEmail = trim((string)($_POST['representativeEmail'] ?? ''));
    $lineId = trim((string)($_POST['lineId'] ?? ''));
    $organization = trim((string)($_POST['organization'] ?? ''));

    if ($bandName === '' || $bandNameKana === '' || $preferredDate === '' || $performanceTime === '' || $representativeName === '' || $lineId === '') {
        http_response_code(400);
        echo "必須項目が未入力です。";
        exit;
    }
    if ($representativeEmail === '' || !filter_var($representativeEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "メールアドレスが正しくありません。";
        exit;
    }




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
    $memberNames = [];
    for ($i = 1; $i <= 7; $i++) {
        $member = trim((string)($_POST["member$i"] ?? ''));
        if (!empty($member)) {
            $stmt = $pdo->prepare("INSERT INTO members (band_id, name) VALUES (?, ?)");
            $stmt->execute([$bandId, $member]);
            $memberNames[] = $member;
        }
    }

    $pdo->commit();

    // 自動返信メール（DBにメールは保存しない）
    $fromEmail = getenv('NORALIVE_MAIL_FROM') ?: 'noreply@noralive.net';
    $fromName = getenv('NORALIVE_MAIL_FROM_NAME') ?: '野良ライヴ';
    $replyTo = getenv('NORALIVE_MAIL_REPLY_TO') ?: null;

    $subject = "【野良ライヴ】バンド申し込みを受け付けました";
    $bodyLines = [
        $representativeName . " 様",
        "",
        "この度は「野良ライヴ」へのバンド申し込みありがとうございます。",
        "以下の内容で受け付けました。",
        "",
        "■ 申し込み内容",
        "・バンド名: " . $bandName,
        "・バンド名（カナ）: " . $bandNameKana,
        "・参加希望日: " . $preferredDate,
        "・演奏時間: " . $performanceTime . "分",
        "・所属団体: " . ($organization !== '' ? $organization : "（未入力）"),
        "・代表者LINE ID: " . $lineId,
        "・メンバー: " . (!empty($memberNames) ? implode(' / ', $memberNames) : "（未入力）"),
        "",
        "内容に誤りがある場合は、このメールへの返信、または運営までご連絡ください。",
        "",
        "野良ライヴ運営",
    ];
    $body = implode("\n", $bodyLines);

    $mailOk = send_japanese_mail($representativeEmail, $subject, $body, $fromEmail, $fromName, $replyTo);
    if (!$mailOk) {
        error_log('[band_submit] auto-reply mail failed: to=' . $representativeEmail);
    }

    echo "🎉 登録完了しました！";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "エラー: " . $e->getMessage();
}
?>
