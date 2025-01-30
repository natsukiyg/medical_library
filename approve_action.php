<?php
include("db_config.php");

// 管理者だけがアクセスできるようにチェック（`user_role`が2なら管理者）
session_start();
if ($_SESSION['user_role'] != 2) {
    echo "アクセス権限がありません。";
    exit();
}

// ボタンが押された場合に処理を実行
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    // 拒否理由を取得（フォームから送信される場合）
    $rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;

    try {
        if ($action == 'approve') {
            // 承認
            $sql = "UPDATE users_table SET is_approved = 1 WHERE memberId = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            echo "ユーザーの承認が完了しました。";
        } elseif ($action == 'reject') {
            // 拒否（未承認にし、拒否理由を保存）
            $sql = "UPDATE users_table SET is_approved = 0, rejection_reason = :rejection_reason WHERE memberId = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':rejection_reason', $rejection_reason);
            $stmt->execute();
            echo "ユーザーは未承認として処理し、拒否理由を記録しました。";
        }

        // 承認後、または拒否後に管理者ページにリダイレクト
        header("Location: approve.php");
        exit();

    } catch (PDOException $e) {
        echo json_encode(["db error" => "{$e->getMessage()}"]);
        exit();
    }
}
?>
