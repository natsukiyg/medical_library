<?php

//セッション開始
session_start();

//セッションからデータを取得
if (!isset($_SESSION['member_data'])) {
    exit("不正なアクセスです。データがありません");
}

//$member_data = $_SESSION['member_data'];

// セッションデータを変数に代入
$member_data = $_SESSION['member_data'];
$name = $member_data['name'];
$gender = $member_data['gender'];
$birthday = $member_data['birthday'];
$email = $member_data['email'];
$address = $member_data['address'];
$whereDidYouHear = $member_data['whereDidYouHear'];
$expectations = $member_data['expectations'];

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録完了</title>
    <link rel="stylesheet" href="./css/confirmation.css">
    <style>
        /* 自動遷移メッセージのスタイル */
        #redirect-message {
            text-align: center;
            font-size: 1.2em;
            color: #fff;
            background-color: #5d3a23; /* ダークブラウン背景 */
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        #redirect-message.show {
            opacity: 1;
        }
    </style>
</head>
<body>
    <h1>登録ありがとうございます！</h1>
    <p>以下の内容を登録しました。</p>
    
    <table>
        <tr><th>氏名</th><td><?= htmlspecialchars($name) ?></td></tr>
        <tr><th>性別</th><td><?= htmlspecialchars($gender) ?></td></tr>
        <tr><th>誕生日</th><td><?= htmlspecialchars($birthday) ?></td></tr>
        <tr><th>メールアドレス</th><td><?= htmlspecialchars($email) ?></td></tr>
        <tr><th>パスワード</th><td><?= str_repeat('●', strlen($member_data['password'])) ?></td></tr>
        <tr><th>住所</th><td><?= htmlspecialchars($address) ?></td></tr>
        <tr><th>知ったきっかけ</th><td><?= htmlspecialchars($whereDidYouHear) ?></td></tr>
        <tr><th>期待する機能</th><td><?= htmlspecialchars($expectations) ?></td></tr>
    </table>

    <!-- 「トップページに戻る」ボタン -->
    <a href="index.php">
        <button class="action-btn"><span>トップページに戻る</span></button>
    </a>

    <!-- 自動遷移メッセージ -->
    <div id="redirect-message">
        このページは10秒後に自動的にトップページに戻ります。
    </div>

    <script>
        // 10秒後にページ遷移
        setTimeout(function(){
            location.href = 'index.php';
        }, 10000);

        // メッセージを表示する
        window.onload = function() {
            setTimeout(function() {
                var messageElement = document.getElementById('redirect-message');
                messageElement.classList.add('show'); // メッセージをフェードイン
            }, 100); // ページが読み込まれた直後に表示
        };
    </script>
</body>
</html>
