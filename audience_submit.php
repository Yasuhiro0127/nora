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
$host = '49.212.150.114';
$dbname = 'xs980818_noralive';
$user = 'xs980818_yasu';
$password = 'pokopixgvp';

$error = false;
$errorMessage = '';

try {
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("接続失敗: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    // フォームデータ取得
    $email = trim((string)($_POST['audienceEmail'] ?? ''));
    $name = trim((string)($_POST['audienceName'] ?? ''));
    $kana = trim((string)($_POST['audienceNameKana'] ?? ''));
    $eventDate = trim((string)($_POST['eventDate'] ?? ''));
    $targetBand = trim((string)($_POST['targetBand'] ?? ''));
    $ticketCountRaw = (string)($_POST['ticketCount'] ?? '');

    // バリデーション
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("メールアドレスが正しくありません。");
    }
    if ($name === '' || $kana === '' || $eventDate === '' || $ticketCountRaw === '') {
        throw new Exception("必須項目が未入力です。");
    }

    $ticketCount = (int)$ticketCountRaw;
    if ($ticketCount <= 0) {
        throw new Exception("枚数が正しくありません。");
    }

    // audiencesテーブルに登録
    $stmt = $conn->prepare("INSERT INTO audiences (email, name, kana, event_date, target_band, ticket_count) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $email, $name, $kana, $eventDate, $targetBand, $ticketCount);
    if (!$stmt->execute()) {
        throw new Exception("申し込みの登録に失敗しました。");
    }
    $stmt->close();
    $conn->close();

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

} catch (Exception $e) {
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
                    観客申し込みが完了しました。<br>
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
