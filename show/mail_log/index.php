<?php
declare(strict_types=1);

require_once __DIR__ . '/../../lib/mail_log.php';

try {
    $pdo = mail_log_pdo_connect();
    ensure_mail_logs_table($pdo);

    $kind = isset($_GET['kind']) ? trim((string)$_GET['kind']) : '';
    $limit = 200;

    if ($kind !== '') {
        $stmt = $pdo->prepare(
            "SELECT id, created_at, kind, to_email, from_email, subject, success, meta_json
             FROM mail_logs
             WHERE kind = :kind
             ORDER BY id DESC
             LIMIT $limit"
        );
        $stmt->execute([':kind' => $kind]);
    } else {
        $stmt = $pdo->query(
            "SELECT id, created_at, kind, to_email, from_email, subject, success, meta_json
             FROM mail_logs
             ORDER BY id DESC
             LIMIT $limit"
        );
    }

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    http_response_code(500);
    echo "エラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール送信履歴</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 16px; }
        .controls { margin-bottom: 12px; }
        .controls a { margin-right: 8px; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 13px; vertical-align: top; }
        th { background: #f7f7f7; position: sticky; top: 0; }
        .wrap { overflow-x: auto; }
        .ok { color: #0a7a0a; font-weight: bold; }
        .ng { color: #b00020; font-weight: bold; }
        code { white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>メール送信履歴</h1>

    <div class="controls">
        <strong>絞り込み:</strong>
        <a href="?">すべて</a>
        <a href="?kind=audience">audience</a>
        <a href="?kind=band">band</a>
        <span style="margin-left:12px; color:#666;">直近<?php echo (int)$limit; ?>件</span>
    </div>

    <div class="wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>日時</th>
                    <th>種別</th>
                    <th>宛先</th>
                    <th>差出人</th>
                    <th>件名</th>
                    <th>結果</th>
                    <th>メタ情報</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $row): ?>
                    <tr>
                        <td><?php echo h((string)$row['id']); ?></td>
                        <td><?php echo h((string)$row['created_at']); ?></td>
                        <td><?php echo h((string)$row['kind']); ?></td>
                        <td><?php echo h((string)$row['to_email']); ?></td>
                        <td><?php echo h((string)$row['from_email']); ?></td>
                        <td><?php echo h((string)$row['subject']); ?></td>
                        <td>
                            <?php if ((int)$row['success'] === 1): ?>
                                <span class="ok">OK</span>
                            <?php else: ?>
                                <span class="ng">NG</span>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo h((string)($row['meta_json'] ?? '')); ?></code></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="8">履歴がありません。</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

