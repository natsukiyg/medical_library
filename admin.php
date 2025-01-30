<?php

// db_config.phpからデータベース接続情報を持ってくる
include("db_config.php"); // db_config.phpの中身を読み込むので、$dbnや$pdoが使えるようになる
session_start();

/* // ログインしていない場合、login.php にリダイレクト
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
 */
// 管理者だけがアクセスできるようにチェック（`user_role`が2なら管理者）
if ($_SESSION['user_role'] != 2) {
    echo "アクセス権限がありません。";
    exit();
}

// 承認待ちユーザーを取得
try {
    $sql = "SELECT memberId, name, email, user_role FROM users_table WHERE is_approved = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    echo json_encode(["db error" => "{$e->getMessage()}"]);
    exit();
}

// ユーザー情報を取得
$user_name = $_SESSION['user_name'];

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ページ</title>
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>

<h1>管理者ページ</h1>
<p>ようこそ、<?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>さん！</p>
<p>以下のリンクから管理者機能にアクセスできます。</p>

<ul>
    <li><a href="registration.php">メンバー登録フォームを見る</a></li>
    <li><a href="read.php">登録データリストを見る</a></li>
    <li><a href="deleted_list.php">削除されたデータリストを見る</a></li>
    <li><a href="approve.php">未承認ユーザー一覧を見る</a></li>
</ul>

<!-- ログアウトボタンとダッシュボード戻るボタンを横並びにするための親要素 -->
<div id="button-container">
    
    <!-- ダッシュボードに戻るボタン -->
    <form method="POST" action="user_dashboard.php">
        <button id="back-btn" style="background: transparent; border: none; padding: 0;">
            <img src="img/dashboard2.png" alt="back-to-dashboard" id="dashboard-img">
        </button>
    </form>

    <!-- ログアウトボタン -->
    <form method="POST" action="logout.php">
        <button id="logout" style="background: transparent; border: none; padding: 0;">
            <img src="img/logout.png" alt="logout" id="logout-img">
        </button>
    </form>
</div>

</body>
</html>
