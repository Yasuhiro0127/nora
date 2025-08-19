<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>バンド登録</title>
</head>

<body>
    <h1>バンド登録フォーム</h1>
    <form action="band_submit.php" method="POST">
        <label>バンド名:<br>
            <input type="text" name="bandName" required>
        </label><br><br>

        <label>バンド名（カナ）:<br>
            <input type="text" name="bandNameKana" required>
        </label><br><br>

        <label>所属団体:<br>
            <input type="text" name="organization">
        </label><br><br>

        <button type="submit">登録</button>
    </form>
</body>

</html>

