<?php
session_start(); // セッション開始

/* //セッション情報が正しく取得できているか確認
echo "<pre>";
print_r($_SESSION);
echo "</pre>"; */

// ログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// データベース接続
include("db_config.php");

// ユーザー情報を取得
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$hospitalName = isset($_SESSION['hospitalName']) ? $_SESSION['hospitalName'] : null;

// 役職登録と病院登録の承認状態を個別に取得
$user_id = $_SESSION['user_id'];

// 役職承認状態を取得
$role_sql = "SELECT approval_status FROM user_role_table WHERE memberId = :memberId";
$role_stmt = $pdo->prepare($role_sql);
$role_stmt->bindParam(':memberId', $user_id, PDO::PARAM_INT);
$role_stmt->execute();
$role_data = $role_stmt->fetch(PDO::FETCH_ASSOC);
$role_approval_status = $role_data ? $role_data['approval_status'] : null;

// 病院登録の承認状態を取得
$hospital_sql = "SELECT approval_status FROM user_hospital_table WHERE memberId = :memberId";
$hospital_stmt = $pdo->prepare($hospital_sql);
$hospital_stmt->bindParam(':memberId', $user_id, PDO::PARAM_INT);
$hospital_stmt->execute();
$hospital_data = $hospital_stmt->fetch(PDO::FETCH_ASSOC);
$hospital_approval_status = $hospital_data ? $hospital_data['approval_status'] : null;

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー用ダッシュボード</title>
    <link rel="stylesheet" href="./css/user_dashboard.css">
</head>
<body>

<h1>ようこそ！ <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>さん</h1>

<p>あなたの権限: 
<?php
    if ($user_role == 0) {
        echo "スタッフ（閲覧のみ）";
    } elseif ($user_role == 1) {
        echo "チームメンバー（編集可能）";
    } elseif ($user_role == 2) {
        echo "管理者";
    }
?>
<!-- 承認状態に応じた表示 -->
<?php if ($role_approval_status == 'approved'): ?>
    <p>（承認済）</p>
<?php elseif ($role_approval_status == 'pending'): ?>
    <p>（承認待ち）</p>
<?php elseif ($role_approval_status == 'denied'): ?>
    <p>（あなたの役職は承認されませんでした。管理者にお問合せください。）</p>
<?php endif; ?>
</p>

<!-- 病院名が設定されていない場合、病院名を登録するリンクを表示 -->
<?php if (!$hospitalName): ?>
    <p><a href="register_hospital_role.php">所属施設を登録する</a></p>
<?php else: ?>
    <p>所属施設: <?php echo htmlspecialchars($hospitalName, ENT_QUOTES, 'UTF-8'); ?>
    <?php if ($hospital_approval_status == 'approved'): ?>
    <p>（承認済）</p>
<?php elseif ($hospital_approval_status == 'pending'): ?>
    <p>（承認待ち）</p>
<?php elseif ($hospital_approval_status == 'denied'): ?>
    <p>（あなたの病院登録は承認されませんでした。管理者にお問合せください。）</p>
<?php endif; ?>
    <p><a href="edit_hospital.php">所属施設情報編集</a></p>
<?php endif; ?>

    <!-- user_role 2の場合、管理者リンク -->
    <?php if ($user_role == 2): ?>
        <p><a href="admin.php">管理者ページ</a></p>
    <?php endif; ?>

    <!-- 病院ごとのマニュアル表示 -->
    <!-- <?php if ($manual): ?>
        <h2><?php echo htmlspecialchars($hospitalName, ENT_QUOTES, 'UTF-8'); ?>のマニュアル</h2>
        <p><?php echo htmlspecialchars($manual['manual'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php else: ?>
        <p>この病院のマニュアルはまだありません。</p>
    <?php endif; ?> -->

<!-- すべてのユーザーにプロフィール編集リンクを表示 -->
<p><a href="edit_profile.php?id=<?php echo $_SESSION['user_id']; ?>">プロフィール編集</a></p>

<!-- ログアウトボタン -->
<form method="POST" action="logout.php">
    <button id="logout-btn" style="background: transparent; border: none; padding: 0;">
        <img src="img/logout.png" alt="logout" id="logout-img">
    </button>
</form>

</body>
</html>
