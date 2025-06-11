<?php
try {
    $host = 'localhost';
    $dbname = 'xs980818_noralive';
    $username = 'xs980818_yasu';
    $password = 'pokopixgvp';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);




    echo "接続完了しました";

    // $sql = "SELECT * FROM `bands`";
    // $stmt = $dbh->query($sql);
    // foreach ($stmt as $row) {
 
    //     // データベースのフィールド名で出力
    //     echo $row['name'].'：'.$row['population'].'人';
       
    //     // 改行を入れる
    //     echo '<br>';
    // }



}catch(PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>


