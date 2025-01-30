<?php
// ログアウト処理
session_start();
session_unset(); // セッション変数を全て削除
session_destroy();  // セッションを破棄

// ログアウト後、トップ画面にリダイレクト
header('Location: index.php');
exit;
?>