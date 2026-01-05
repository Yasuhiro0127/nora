<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/mailer.php';
require_once __DIR__ . '/lib/mail_log.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// DB接続
$host = 'localhost';
$dbname = 'xs980818_noralive';
$user = 'xs980818_yasu';         // ← 修正済みユーザー名
$password = 'pokopixgvp';     


$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}
@$conn->set_charset('utf8mb4');

// フォームデータ取得
$email = trim((string)($_POST['audienceEmail'] ?? ''));
$name = trim((string)($_POST['audienceName'] ?? ''));
$kana = trim((string)($_POST['audienceNameKana'] ?? ''));
$eventDate = trim((string)($_POST['eventDate'] ?? ''));
$targetBand = trim((string)($_POST['targetBand'] ?? ''));
$ticketCountRaw = (string)($_POST['ticketCount'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "メールアドレスが正しくありません。";
    exit;
}
if ($name === '' || $kana === '' || $eventDate === '' || $ticketCountRaw === '') {
    http_response_code(400);
    echo "必須項目が未入力です。";
    exit;
}

$ticketCount = (int)$ticketCountRaw;
if ($ticketCount <= 0) {
    http_response_code(400);
    echo "枚数が正しくありません。";
    exit;
}

// audiencesテーブルに登録
$stmt = $conn->prepare("INSERT INTO audiences (email, name, kana, event_date, target_band, ticket_count) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $email, $name, $kana, $eventDate, $targetBand, $ticketCount);
$ok = $stmt->execute();
$stmt->close();

$conn->close();

if (!$ok) {
    http_response_code(500);
    echo "申し込みの登録に失敗しました。時間をおいて再度お試しください。";
    exit;
}

// 自動返信メール
$fromEmail = getenv('NORALIVE_MAIL_FROM') ?: 'noreply@noralive.net';
$fromName = getenv('NORALIVE_MAIL_FROM_NAME') ?: '野良ライヴ';
$replyTo = getenv('NORALIVE_MAIL_REPLY_TO') ?: null;

$subject = "【野良ライヴ】観客申し込みを受け付けました";
$bodyLines = [
    $name . " 様",
    "",
    "この度は「野良ライヴ」観客申し込みありがとうございます。",
    "以下の内容で受け付けました。",
    "",
    "■ 申し込み内容",
    "・日付: " . $eventDate,
    "・枚数: " . $ticketCount . "枚",
    "・目的のバンド: " . ($targetBand !== '' ? $targetBand : "（未入力）"),
    "",
    "内容に誤りがある場合は、このメールへの返信、または運営までご連絡ください。",
    "",
    "野良ライヴ運営",
];
$body = implode("\n", $bodyLines);

$mailOk = send_japanese_mail($email, $subject, $body, $fromEmail, $fromName, $replyTo);
if (!$mailOk) {
    error_log('[audience_submit] auto-reply mail failed: to=' . $email);
}

// 送信履歴を保存（失敗しても申し込み自体は成功扱い）
try {
    $logPdo = mail_log_pdo_connect();
    insert_mail_log(
        $logPdo,
        'audience',
        $email,
        $fromEmail,
        $subject,
        $mailOk,
        [
            'name' => $name,
            'eventDate' => $eventDate,
            'ticketCount' => $ticketCount,
            'targetBand' => $targetBand,
        ]
    );
} catch (Throwable $e) {
    error_log('[audience_submit] mail log failed: ' . $e->getMessage());
}

echo "観客申し込みが完了しました。";
?>
