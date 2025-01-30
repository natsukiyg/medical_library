<?php
include("db_config.php");

// 管理者だけがアクセスできるようにチェック（`user_role`が2なら管理者）
session_start();
if ($_SESSION['user_role'] != 2) {
    echo "アクセス権限がありません。";
    exit();
}

// 承認待ちユーザーを取得
try {
    $sql = "SELECT memberId, name, email, hospitalId, user_role, registered_at FROM users_table WHERE is_approved = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    echo json_encode(["db error" => "{$e->getMessage()}"]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>未承認ユーザー一覧</title>
    <link rel="stylesheet" href="./css/admin.css">
    <script type="text/javascript">
        // 承認・拒否時に確認ポップアップを表示
        function confirmAction(userName, hospitalId, userRole, action) {
            let role = "";
            if (userRole == 1) {
                role = "チームメンバー";
            } else if (userRole == 2) {
                role = "管理者";
            } else if (userRole == 0) {
                role = "スタッフ";
            }
            let actionText = (action == 'approve') ? "承認" : "拒否";
            let message = userName + " さん（所属施設名：" + hospitalId + "）の " + role + " を " + actionText + " しますか？";

            return confirm(message);  // ユーザーがOKを押した場合のみ送信
        }

        // 拒否理由のフォーム表示制御
        function showRejectionReasonForm(userId) {
            // 拒否理由フォームの表示・非表示を制御
            const form = document.getElementById("rejection-form-" + userId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>

<h1>未承認ユーザー一覧</h1>

<!-- 「管理者ページ」リンクを追加 -->
<div class="admin-link">
    <a href="admin.php" class="action-btn">管理者ページ</a>
</div>

<!-- 承認待ちユーザーをテーブルで表示 -->
<table border="1">
    <tr>
        <th>名前</th>
        <th>メールアドレス</th>
        <th>所属施設</th>
        <th>権限</th>
        <th>登録日時</th> <!-- 登録日時カラムを追加 -->
        <th>操作</th>
    </tr>

    <?php foreach ($users as $user): ?>
    <tr>
        <td><?php echo htmlspecialchars($user['name']); ?></td>
        <td><?php echo htmlspecialchars($user['email']); ?></td>
        <td>
            <?php echo htmlspecialchars($user['hospitalId']) ? htmlspecialchars($user['hospitalId']) : "未設定"; ?>
        </td>
        <td>
            <?php
                if ($user['user_role'] == 1) echo "チームメンバー";
                else if ($user['user_role'] == 2) echo "管理者";
                else if ($user['user_role'] == 0) echo "スタッフ";
            ?>
        </td>
        <td>
            <!-- 登録日時を表示（`registered_at`をフォーマットして表示） -->
            <?php echo date('Y-m-d H:i:s', strtotime($user['registered_at'])); ?>
        </td>
        <td>
            <!-- 承認・拒否ボタン -->
            <form action="approve_action.php" method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['memberId']; ?>">
                
                <!-- 承認ボタン -->
                <button type="submit" name="action" value="approve" onclick="return confirmAction('<?php echo addslashes($user['name']); ?>', '<?php echo addslashes($user['hospitalId']); ?>', <?php echo $user['user_role']; ?>, 'approve');">
                    承認
                </button>

                <!-- 拒否ボタンと拒否理由 -->
                <button type="button" onclick="showRejectionReasonForm(<?php echo $user['memberId']; ?>);">
                    拒否
                </button>

                <!-- 拒否理由の入力フォーム（初期状態では非表示） -->
                <div id="rejection-form-<?php echo $user['memberId']; ?>" style="display:none;">
                    <textarea name="rejection_reason" placeholder="拒否理由を入力してください"></textarea>
                    <button type="submit" name="action" value="reject">送信</button>
                </div>
            </form>

            <script>
                function showRejectionReasonForm(userId) {
                    // 拒否ボタンを押すと、拒否理由フォームが表示される
                    document.getElementById("rejection-form-" + userId).style.display = 'block';
                }
            </script>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
