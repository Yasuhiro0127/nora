<?php
declare(strict_types=1);

/**
 * 日本語を含むメールを送信する簡易ヘルパー。
 * - mbstring が有効なら mb_send_mail を優先
 * - 失敗時は false を返す（例外は投げない）
 */
function send_japanese_mail(
    string $to,
    string $subject,
    string $body,
    string $fromEmail,
    string $fromName = '野良ライヴ',
    ?string $replyTo = null
): bool {
    $to = trim($to);
    $fromEmail = trim($fromEmail);
    $fromName = trim($fromName);
    $replyTo = $replyTo !== null ? trim($replyTo) : null;

    if ($to === '' || $fromEmail === '' || $subject === '' || $body === '') {
        return false;
    }

    // Header injection 対策（改行を含む場合は拒否）
    foreach ([$to, $fromEmail, $replyTo ?? ''] as $v) {
        if (preg_match("/\r|\n/", $v)) {
            return false;
        }
    }

    $encodedFromName = function_exists('mb_encode_mimeheader')
        ? mb_encode_mimeheader($fromName, 'UTF-8', 'B', "\r\n")
        : $fromName;

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';
    $headers[] = 'From: ' . $encodedFromName . ' <' . $fromEmail . '>';
    if ($replyTo) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    $headerStr = implode("\r\n", $headers);

    if (function_exists('mb_language')) {
        @mb_language('Japanese');
    }
    if (function_exists('mb_internal_encoding')) {
        @mb_internal_encoding('UTF-8');
    }

    if (function_exists('mb_send_mail')) {
        return (bool) @mb_send_mail($to, $subject, $body, $headerStr);
    }

    return (bool) @mail($to, $subject, $body, $headerStr);
}

