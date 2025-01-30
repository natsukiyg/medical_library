<?php
// DB接続設定
include("db_config.php");

session_start();

// ログインしていない場合、login.php にリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 編集するメンバーIDをGETパラメータから取得
$id = $_SESSION['user_id'];

// SQLでメンバー情報を取得
$sql = "SELECT * FROM users_table WHERE memberId = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// メンバーが存在しない場合
if (!$member) {
    exit("該当するメンバーが見つかりません。");
}

// hospitalIdを使ってhospitalNameを取得
$hospitalId = $member['hospitalId'];
$sql_hospital = "SELECT hospitalName FROM hospital_table WHERE hospitalId = :hospitalId";
$stmt_hospital = $pdo->prepare($sql_hospital);
$stmt_hospital->bindValue(':hospitalId', $hospitalId, PDO::PARAM_INT);
$stmt_hospital->execute();
$hospital = $stmt_hospital->fetch(PDO::FETCH_ASSOC);
$hospitalName = $hospital ? $hospital['hospitalName'] : ''; // hospitalNameが見つからない場合は空にする

// 権限の表示用配列
$user_roles = [
    0 => "スタッフ（閲覧のみ）",
    1 => "チームメンバー（編集可能）",
    2 => "管理者"
];

// 編集後のデータを処理する
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // POSTデータを取得
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $address = $_POST['address'];
    $hospitalName = $_POST['hospitalName'];
    $user_role = $_POST['user_role'];

    // バリデーション：メールアドレスの形式チェック
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("無効なメールアドレスです");
    }

    // パスワードが空でない場合のみ、更新処理を行う
    if (!empty($_POST['password'])) {
        $password = password_hash($password, PASSWORD_DEFAULT);  // パスワードをハッシュ化
    } else {
        // パスワードが送信されていない場合、元のパスワードを保持
        $password = $member['password'];  // 元のパスワードを使う
    }

    // hospitalNameを基にhospitalIdを取得
    $sql_hospital_check = "SELECT hospitalId FROM hospital_table WHERE hospitalName = :hospitalName";
    $stmt_hospital_check = $pdo->prepare($sql_hospital_check);
    $stmt_hospital_check->bindValue(':hospitalName', $hospitalName, PDO::PARAM_STR);
    $stmt_hospital_check->execute();
    $hospital_data = $stmt_hospital_check->fetch(PDO::FETCH_ASSOC);

    if ($hospital_data) {
        // 既存の病院がある場合
        $hospitalId = $hospital_data['hospitalId'];
    } else {
        // 新規病院を登録
        $sql_insert_hospital = "INSERT INTO hospital_table (hospitalName) VALUES (:hospitalName)";
        $stmt_insert_hospital = $pdo->prepare($sql_insert_hospital);
        $stmt_insert_hospital->bindValue(':hospitalName', $hospitalName, PDO::PARAM_STR);
        $stmt_insert_hospital->execute();

        // 新規に追加した病院のIDを取得
        $hospitalId = $pdo->lastInsertId();
    }

    //タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');
    $updated_at = date('Y-m-d H:i:s');

    // SQLを実行してデータを更新
    $sql = "UPDATE users_table SET name = :name, gender = :gender, birthday = :birthday, 
            email = :email, password = :password, address = :address, hospitalId = :hospitalId, 
            user_role = :user_role, updated_at = :updated_at WHERE memberId = :id";
    $stmt = $pdo->prepare($sql);

    // バインド変数を設定
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
    $stmt->bindValue(':birthday', $birthday, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
    $stmt->bindValue(':hospitalId', $hospitalId, PDO::PARAM_INT);
    $stmt->bindValue(':user_role', $user_role, PDO::PARAM_INT);
    $stmt->bindValue(':updated_at', $updated_at, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    try {
        $stmt->execute();
        echo '<script type="text/javascript">
                alert("更新が完了しました！");
                setTimeout(function() {
                    window.location.href = "user_dashboard.php";
                }, 1500); // 1.5秒後にリダイレクト
              </script>';
        exit;
    } catch (PDOException $e) {
        echo "更新に失敗しました: " . $e->getMessage();
        exit;
    }
}
?>


<!-- 編集フォーム -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メンバー情報編集</title>
    <link rel="stylesheet" href="./css/edit_profile.css">
</head>
<body>

<h1>メンバー情報編集</h1>

<form action="edit_profile.php?id=<?php echo $id; ?>" method="POST">
    <label for="name">名前:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" required><br>

    <label for="gender">性別:</label>
    <select id="gender" name="gender" required>
        <option value="男性" <?php echo $member['gender'] == 'male' ? 'selected' : ''; ?>>男性</option>
        <option value="女性" <?php echo $member['gender'] == 'female' ? 'selected' : ''; ?>>女性</option>
        <option value="無回答" <?php echo $member['gender'] == 'none' ? 'selected' : ''; ?>>無回答</option>
    </select><br>
    
    <label for="birthday">誕生日:</label>
    <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($member['birthday']); ?>" required><br>

    <label for="email">メールアドレス:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required><br>

    <label for="password">パスワード:</label>
    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($member['password']); ?>" required><br>

    <label for="address">住所:</label>
    <select id="address" name="address" required>
        <option value="">選択してください</option>
        <option value="北海道" <?php echo $member['address'] == '北海道' ? 'selected' : ''; ?>>北海道</option>
        <option value="青森県" <?php echo $member['address'] == '青森県' ? 'selected' : ''; ?>>青森県</option>
        <option value="岩手県" <?php echo $member['address'] == '岩手県' ? 'selected' : ''; ?>>岩手県</option>
        <option value="宮城県" <?php echo $member['address'] == '宮城県' ? 'selected' : ''; ?>>宮城県</option>
        <option value="秋田県" <?php echo $member['address'] == '秋田県' ? 'selected' : ''; ?>>秋田県</option>
        <option value="山形県" <?php echo $member['address'] == '山形県' ? 'selected' : ''; ?>>山形県</option>
        <option value="福島県" <?php echo $member['address'] == '福島県' ? 'selected' : ''; ?>>福島県</option>
        <option value="茨城県" <?php echo $member['address'] == '茨城県' ? 'selected' : ''; ?>>茨城県</option>
        <option value="栃木県" <?php echo $member['address'] == '栃木県' ? 'selected' : ''; ?>>栃木県</option>
        <option value="群馬県" <?php echo $member['address'] == '群馬県' ? 'selected' : ''; ?>>群馬県</option>
        <option value="埼玉県" <?php echo $member['address'] == '埼玉県' ? 'selected' : ''; ?>>埼玉県</option>
        <option value="千葉県" <?php echo $member['address'] == '千葉県' ? 'selected' : ''; ?>>千葉県</option>
        <option value="東京都" <?php echo $member['address'] == '東京都' ? 'selected' : ''; ?>>東京都</option>
        <option value="神奈川県" <?php echo $member['address'] == '神奈川県' ? 'selected' : ''; ?>>神奈川県</option>
        <option value="新潟県" <?php echo $member['address'] == '新潟県' ? 'selected' : ''; ?>>新潟県</option>
        <option value="富山県" <?php echo $member['address'] == '富山県' ? 'selected' : ''; ?>>富山県</option>
        <option value="石川県" <?php echo $member['address'] == '石川県' ? 'selected' : ''; ?>>石川県</option>
        <option value="福井県" <?php echo $member['address'] == '福井県' ? 'selected' : ''; ?>>福井県</option>
        <option value="山梨県" <?php echo $member['address'] == '山梨県' ? 'selected' : ''; ?>>山梨県</option>
        <option value="長野県" <?php echo $member['address'] == '長野県' ? 'selected' : ''; ?>>長野県</option>
        <option value="岐阜県" <?php echo $member['address'] == '岐阜県' ? 'selected' : ''; ?>>岐阜県</option>
        <option value="静岡県" <?php echo $member['address'] == '静岡県' ? 'selected' : ''; ?>>静岡県</option>
        <option value="愛知県" <?php echo $member['address'] == '愛知県' ? 'selected' : ''; ?>>愛知県</option>
        <option value="三重県" <?php echo $member['address'] == '三重県' ? 'selected' : ''; ?>>三重県</option>
        <option value="滋賀県" <?php echo $member['address'] == '滋賀県' ? 'selected' : ''; ?>>滋賀県</option>
        <option value="京都府" <?php echo $member['address'] == '京都府' ? 'selected' : ''; ?>>京都府</option>
        <option value="大阪府" <?php echo $member['address'] == '大阪府' ? 'selected' : ''; ?>>大阪府</option>
        <option value="兵庫県" <?php echo $member['address'] == '兵庫県' ? 'selected' : ''; ?>>兵庫県</option>
        <option value="奈良県" <?php echo $member['address'] == '奈良県' ? 'selected' : ''; ?>>奈良県</option>
        <option value="和歌山県" <?php echo $member['address'] == '和歌山県' ? 'selected' : ''; ?>>和歌山県</option>
        <option value="鳥取県" <?php echo $member['address'] == '鳥取県' ? 'selected' : ''; ?>>鳥取県</option>
        <option value="島根県" <?php echo $member['address'] == '島根県' ? 'selected' : ''; ?>>島根県</option>
        <option value="岡山県" <?php echo $member['address'] == '岡山県' ? 'selected' : ''; ?>>岡山県</option>
        <option value="広島県" <?php echo $member['address'] == '広島県' ? 'selected' : ''; ?>>広島県</option>
        <option value="山口県" <?php echo $member['address'] == '山口県' ? 'selected' : ''; ?>>山口県</option>
        <option value="徳島県" <?php echo $member['address'] == '徳島県' ? 'selected' : ''; ?>>徳島県</option>
        <option value="香川県" <?php echo $member['address'] == '香川県' ? 'selected' : ''; ?>>香川県</option>
        <option value="愛媛県" <?php echo $member['address'] == '愛媛県' ? 'selected' : ''; ?>>愛媛県</option>
        <option value="高知県" <?php echo $member['address'] == '高知県' ? 'selected' : ''; ?>>高知県</option>
        <option value="福岡県" <?php echo $member['address'] == '福岡県' ? 'selected' : ''; ?>>福岡県</option>
        <option value="佐賀県" <?php echo $member['address'] == '佐賀県' ? 'selected' : ''; ?>>佐賀県</option>
        <option value="長崎県" <?php echo $member['address'] == '長崎県' ? 'selected' : ''; ?>>長崎県</option>
        <option value="熊本県" <?php echo $member['address'] == '熊本県' ? 'selected' : ''; ?>>熊本県</option>
        <option value="大分県" <?php echo $member['address'] == '大分県' ? 'selected' : ''; ?>>大分県</option>
        <option value="宮崎県" <?php echo $member['address'] == '宮崎県' ? 'selected' : ''; ?>>宮崎県</option>
        <option value="鹿児島県" <?php echo $member['address'] == '鹿児島県' ? 'selected' : ''; ?>>鹿児島県</option>
        <option value="沖縄県" <?php echo $member['address'] == '沖縄県' ? 'selected' : ''; ?>>沖縄県</option>
    </select><br>

    <label for="hospitalName">所属施設:</label>
    <input type="text" id="hospitalName" name="hospitalName" value="<?php echo htmlspecialchars($hospitalName); ?>" required><br>

    <label for="user_role">権限:</label>
    <select id="user_role" name="user_role" required>
        <?php
        foreach ($user_roles as $key => $role) {
            $selected = ($member['user_role'] == $key) ? 'selected' : ''; // 現在の役割を選択状態にする
            echo "<option value='{$key}' {$selected}>{$role}</option>";
        }
        ?>
    </select><br>

    <!-- 知ったきっかけ（読み取り専用） -->
    <label for="whereDidYouHear">知ったきっかけ:</label>
    <input type="text" id="whereDidYouHear" name="whereDidYouHear" value="<?php echo htmlspecialchars($member['whereDidYouHear']); ?>" readonly><br>

    <!-- 期待する機能（読み取り専用） -->
    <label for="expectations">期待する機能:</label>
    <input type="text" id="expectations" name="expectations" value="<?php echo htmlspecialchars($member['expectations']); ?>" readonly><br>

    <input type="submit" value="更新">
</form>

</body>
</html>