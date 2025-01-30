<?php
session_start(); // セッション開始

// データベース接続設定
include('db_config.php');

//$pdo->exec("USE {$dbName}"); // データベースを選択


// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? ''; // メールアドレス
    $password = $_POST['password'] ?? ''; // パスワード

    // 入力されたメールアドレスを使ってユーザーを検索
    try {
        $sql = "SELECT * FROM users_table WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       // ユーザーが見つかった場合、パスワードの照合
       if ($user && password_verify($password, $user['password'])) {

            // パスワードが一致した場合、セッションにユーザー情報をセット
            $_SESSION['user_name'] = $user['name']; // ユーザー名を保存
            $_SESSION['user_id'] = $user['memberId'];
            $_SESSION['user_role'] = $user['user_role'];  // 役割を保存（0, 1, 2）
            $_SESSION['is_approved'] = $user['is_approved']; // 承認状態を保存（0:未承認, 1:承認済）
            $_SESSION['hospitalName'] = $user['hospitalName']; // 病院名を保存

            // 全ユーザーがユーザーページに遷移
            header('Location:user_dashboard.php'); // ユーザーのダッシュボードなどにリダイレクト
            exit;
        } else {
            // パスワードが一致しない場合
            echo "メールアドレスまたはパスワードが間違っています";
        }
    } catch (PDOException $e) {
        //データベース接続やSQL実行時のエラーがあった場合
        echo "データベースエラー: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザーログイン</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<h2>ユーザーログイン</h2>
<?php
 if (isset($error_message)) { echo "<p style='color: red;'>$error_message</p>"; } 
?>

<form method="POST">
    <label for="email">メールアドレス：</label>
    <input type="email" name="email" id="email" placeholder="メールアドレス" required><br>

    <label for="password">パスワード：</label>
    <input type="password" name="password" id="password" placeholder="英数字8〜30文字" required><br>

    <button type="submit">ログイン</button>
</form>

</body>
</html>
