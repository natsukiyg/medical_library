<?php

// db_config.phpからデータベース接続情報を持ってくる
include("db_config.php"); // db_config.phpの中身を読み込むので、$dbnや$pdoが使えるようになる

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メンバー登録フォーム(DB ver3.0)</title>
    <link rel="stylesheet" href="./css/registration.css">
</head>
<body>
    <h1>メンバー登録フォーム</h1>

    <form action="create.php" method="POST">
        <!-- メンバー情報 -->
        <fieldset>
            <legend>メンバー情報</legend>
            <label for="name">氏名：</label>
            <input type="text" id="name" name="name" required><br>

            <label for="gender">性別：</label>
            <input type="radio" id="male" name="gender" value="男性" required> 男性
            <input type="radio" id="female" name="gender" value="女性"> 女性
            <input type="radio" id="none" name="gender" value="無回答"> 無回答<br>

            <label for="birthday">誕生日：</label>
            <input type="date" id="birthday" name="birthday" required><br>

            <label for="email">メールアドレス：</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">パスワード：</label>
            <input type="password" id="password" name="password" pattern="^(?=.*[a-zA-Z])(?=.*\d)[A-Za-z\d]{8,30}$" title="英数字を含めて8〜30文字で入力してください" required><br>

            <label for="address">住所（都道府県）：</label>
            <select id="address" name="address" required>
                <option value="">選択してください</option>
                <option value="北海道">北海道</option>
                <option value="青森県">青森県</option>
                <option value="岩手県">岩手県</option>
                <option value="宮城県">宮城県</option>
                <option value="秋田県">秋田県</option>
                <option value="山形県">山形県</option>
                <option value="福島県">福島県</option>
                <option value="茨城県">茨城県</option>
                <option value="栃木県">栃木県</option>
                <option value="群馬県">群馬県</option>
                <option value="埼玉県">埼玉県</option>
                <option value="千葉県">千葉県</option>
                <option value="東京都">東京都</option>
                <option value="神奈川県">神奈川県</option>
                <option value="新潟県">新潟県</option>
                <option value="富山県">富山県</option>
                <option value="石川県">石川県</option>
                <option value="福井県">福井県</option>
                <option value="山梨県">山梨県</option>
                <option value="長野県">長野県</option>
                <option value="岐阜県">岐阜県</option>
                <option value="静岡県">静岡県</option>
                <option value="愛知県">愛知県</option>
                <option value="三重県">三重県</option>
                <option value="滋賀県">滋賀県</option>
                <option value="京都府">京都府</option>
                <option value="大阪府">大阪府</option>
                <option value="兵庫県">兵庫県</option>
                <option value="奈良県">奈良県</option>
                <option value="和歌山県">和歌山県</option>
                <option value="鳥取県">鳥取県</option>
                <option value="島根県">島根県</option>
                <option value="岡山県">岡山県</option>
                <option value="広島県">広島県</option>
                <option value="山口県">山口県</option>
                <option value="徳島県">徳島県</option>
                <option value="香川県">香川県</option>
                <option value="愛媛県">愛媛県</option>
                <option value="高知県">高知県</option>
                <option value="福岡県">福岡県</option>
                <option value="佐賀県">佐賀県</option>
                <option value="長崎県">長崎県</option>
                <option value="熊本県">熊本県</option>
                <option value="大分県">大分県</option>
                <option value="宮崎県">宮崎県</option>
                <option value="鹿児島県">鹿児島県</option>
                <option value="沖縄県">沖縄県</option>
            </select><br>

            <label for="hospitalName">所属施設：</label>
            <input type="text" id="hospitalName" name="hospitalName" required><br>

            <!-- ユーザー権限 -->
            <label for="user_role">権限：</label><br>
            <input type="radio" id="role_0" name="user_role" value="0" required> スタッフ（閲覧のみ）
            <input type="radio" id="role_1" name="user_role" value="1"> チームメンバー（編集可能）
            <input type="radio" id="role_2" name="user_role" value="2"> 管理者<br>
        </fieldset>

        <!-- アンケート情報 -->
        <fieldset>
            <legend>アンケート</legend>
            <label for="whereDidYouHear">どこで知りましたか？</label><br>
            <input type="radio" id="online" name="whereDidYouHear" value="オンライン" required> オンライン
            <input type="radio" id="friend" name="whereDidYouHear" value="友人"> 友人
            <input type="radio" id="advertisement" name="whereDidYouHear" value="広告"> 広告
            <input type="radio" id="others" name="whereDidYouHear" value="その他"> その他<br>

            <label for="expectations">どんな機能を期待しますか？</label><br>
            <textarea id="expectations" name="expectations" rows="4" cols="50" required></textarea><br>
        </fieldset>

        <button type="submit">登録</button>
    </form>

    <!-- 管理者ページリンク -->
    <p><a href="admin.php">管理者ページ</a></p>

</body>
</html>
