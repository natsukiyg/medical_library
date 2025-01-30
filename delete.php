<?php

// DB接続設定
include("db_config.php");

session_start();

/* // ログインしていない場合、login.php にリダイレクト
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
} */

// 削除するメンバーIDをGETパラメータから取得
if (!isset($_GET['id'])) {
    exit("メンバーIDが指定されていません。");
}

$id = $_GET['id'];

// DB接続設定
include("db_config.php");

// SQLでメンバー情報を削除
$sql = "DELETE FROM users_table WHERE memberId = :memberId";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':memberId', $id, PDO::PARAM_INT);

try {
    $stmt->execute();
    header("Location: read.php"); // 削除後は一覧ページにリダイレクト
    exit;
} catch (PDOException $e) {
    echo "削除に失敗しました: " . $e->getMessage();
    exit;
}
?>
