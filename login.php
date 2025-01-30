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

            //user_role と approval_status をuser_role_table、またはuser_hospital_tableから取得
            $user_id = $user['memberId'];

            // user_role_table からuser_roleとapproval_statusを取得
            $role_sql = "SELECT user_role, approval_status FROM user_role_table WHERE memberId = :memberId";
            $role_stmt = $pdo->prepare($role_sql);
            $role_stmt->bindParam(':memberId', $user['memberId'], PDO::PARAM_INT);
            $role_stmt->execute();

            $role = $role_stmt->fetch(PDO::FETCH_ASSOC);

            /* //デバッグ用
            echo "<pre>";
            print_r($role);
            echo "</pre>"; */

            //user_roleとapproval_statusが取得できた場合、セッションにセット
            if ($role) {
                $_SESSION['user_role'] = $role['user_role'];  // 役割を保存（0, 1, 2）
                $_SESSION['approval_status'] = $role['approval_status']; // 承認状態を保存（pending,approved,denied）
            } else {
                $_SESSION['user_role'] = null; // 役割がなければnull
                $_SESSION['approval_status'] = null; // 承認状態がなければnull
            }
 
            // 病院名の取得（user_hospital_table から取得）
            $hospital_sql = "SELECT h.hospitalName FROM user_hospital_table uht
                             JOIN hospital_table h ON uht.hospitalId = h.hospitalId
                             WHERE uht.memberId = :memberId";
            $hospital_stmt = $pdo->prepare($hospital_sql);
            $hospital_stmt->bindParam(':memberId', $user['memberId'], PDO::PARAM_INT);
            $hospital_stmt->execute();
            $hospital = $hospital_stmt->fetch(PDO::FETCH_ASSOC);

            // 病院名が取得できた場合、セッションにセット
            if ($hospital) {
                $_SESSION['hospitalName'] = $hospital['hospitalName'];
            } else {
                $_SESSION['hospitalName'] = null; // 病院名がなければnull
            }    

            /* //セッションの内容をデバッグ出力（確認用）
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";  */

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
