<?php
try {
    $host = 'localhost';
    $dbname = 'xs980818_noralive';
    $username = 'xs980818_yasu';
    $password = 'pokopixgvp';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);




    echo "接続完了しました";



}catch(PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>