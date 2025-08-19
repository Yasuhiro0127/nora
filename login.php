<?php
session_start();

$host = 'localhost';
$dbname = 'xs980818_noralive';
$user = 'xs980818_yasu';
$password = 'pokopixgvp';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('接続失敗: ' . $conn->connect_error);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, password FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($userId, $hash);
    if ($stmt->fetch() && password_verify($pass, $hash)) {
        $_SESSION['user_id'] = $userId;
        header('Location: index.html');
        exit;
    } else {
        $error = 'メールアドレスまたはパスワードが違います。';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
</head>
<body>
<?php if ($error): ?>
    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<form method="post" action="login.php">
    <label>メールアドレス: <input type="email" name="email" required></label><br>
    <label>パスワード: <input type="password" name="password" required></label><br>
    <button type="submit">ログイン</button>
</form>
</body>
</html>
