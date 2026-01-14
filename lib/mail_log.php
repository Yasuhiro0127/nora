<?php
declare(strict_types=1);

/**
 * メール送信履歴をDBへ保存するヘルパー（PDO）。
 * - テーブルが無ければ自動作成
 * - 個人情報を増やしすぎないため、本文は保存しない（meta_jsonに最小限の情報のみ）
 */

function mail_log_pdo_connect(): PDO
{
    $host = 'localhost';
    $dbname = 'xs980818_noralive';
    $username = 'xs980818_yasu';
    $password = 'pokopixgvp';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function ensure_mail_logs_table(PDO $pdo): void
{
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS mail_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  kind VARCHAR(32) NOT NULL,
  to_email VARCHAR(255) NOT NULL,
  from_email VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  success TINYINT(1) NOT NULL,
  meta_json TEXT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
SQL;
    $pdo->exec($sql);
}

function insert_mail_log(
    PDO $pdo,
    string $kind,
    string $toEmail,
    string $fromEmail,
    string $subject,
    bool $success,
    array $meta = []
): void {
    ensure_mail_logs_table($pdo);

    $kind = mb_substr(trim($kind), 0, 32);
    $toEmail = mb_substr(trim($toEmail), 0, 255);
    $fromEmail = mb_substr(trim($fromEmail), 0, 255);
    $subject = mb_substr(trim($subject), 0, 255);

    $metaJson = null;
    if (!empty($meta)) {
        $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO mail_logs (kind, to_email, from_email, subject, success, meta_json)
         VALUES (:kind, :to_email, :from_email, :subject, :success, :meta_json)"
    );
    $stmt->execute([
        ':kind' => $kind,
        ':to_email' => $toEmail,
        ':from_email' => $fromEmail,
        ':subject' => $subject,
        ':success' => $success ? 1 : 0,
        ':meta_json' => $metaJson,
    ]);
}

