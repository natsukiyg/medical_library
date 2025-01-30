<?php
// DB接続設定
include("db_config.php"); // DB接続設定ファイル
session_start();

/* // ログインしていない場合、login.php にリダイレクト
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
} */

// 管理者かどうかの確認（管理者かどうかはセッションで確認）
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2) {
    exit("アクセス権限がありません。");
}

// 編集するメンバーIDをGETパラメータから取得
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit("メンバーIDが指定されていません。");
}

$id = $_GET['id'];

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

// 施設一覧を取得して、ドロップダウンに表示
$sql_all_hospitals = "SELECT * FROM hospital_table";
$stmt_hospitals = $pdo->prepare($sql_all_hospitals);
$stmt_hospitals->execute();
$hospitals = $stmt_hospitals->fetchAll(PDO::FETCH_ASSOC);

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
    $address = $_POST['address'];
    $hospitalId = $_POST['hospitalId'];  // 施設IDを選択する
    $user_role = $_POST['user_role'];

    // バリデーション：メールアドレスの形式チェック
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("無効なメールアドレスです");
    }
    // パスワードが空でない場合のみ、更新処理を行う
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];  // 新しいパスワードを取得
        // パスワードをハッシュ化する場合、ここで処理する
        $password = password_hash($password, PASSWORD_DEFAULT);  // パスワードをハッシュ化（必要なら）
    } else {
        // パスワードが送信されていない場合、元のパスワードを保持
        $password = $member['password'];  // 元のパスワードを使う
    }
    
    //タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');
    
    //現在の日時を取得（更新日時として使用）
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
    $stmt->bindValue(':hospitalId', $hospitalId, PDO::PARAM_INT);  // 施設IDを更新
    $stmt->bindValue(':user_role', $user_role, PDO::PARAM_INT);
    $stmt->bindValue(':updated_at', $updated_at, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
    try {
        $stmt->execute();

        // 更新成功したら、ポップアップを表示し、一定時間後に登録データリストへ遷移
        echo '<script type="text/javascript">
                alert("更新が完了しました！");
                setTimeout(function() {
                    window.location.href = "read.php"; // 一覧ページにリダイレクト
                }, 1500); // 1.5秒後にリダイレクト
              </script>';
        exit;
    } catch (PDOException $e) {
        // エラーが発生した場合、エラーメッセージを表示して終了
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
    <form action="edit_profile_admin.php?id=<?php echo $id; ?>" method="POST">
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
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($member['address']); ?>" required><br>
    
        <label for="hospitalId">所属施設:</label>
        <select id="hospitalId" name="hospitalId" required>
            <?php foreach ($hospitals as $hospital): ?>
                <option value="<?php echo $hospital['hospitalId']; ?>" <?php echo $hospital['hospitalId'] == $member['hospitalId'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($hospital['hospitalName']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        
        <label for="user_role">権限:</label>
        <select id="user_role" name="user_role" required>
            <?php
            foreach ($user_roles as $key => $role) {
                $selected = ($member['user_role'] == $key) ? 'selected' : ''; // 現在の役割を選択状態にする
                echo "<option value='{$key}' {$selected}>{$role}</option>";
            }
            ?>
        </select><br>
        
        <label for="whereDidYouHear">知ったきっかけ:</label>
        <input type="text" id="whereDidYouHear" name="whereDidYouHear" value="<?php echo htmlspecialchars($member['whereDidYouHear']); ?>" readonly><br>
        
        <label for="expectations">期待する機能:</label>
        <input type="text" id="expectations" name="expectations" value="<?php echo htmlspecialchars($member['expectations']); ?>" readonly><br>

        <input type="submit" value="更新">
    </form>
</body>
</html>
