<?php

// DB接続設定
include("db_config.php");

// 削除されたデータを取得（deleted_at が NULL でない）
$sql = 'SELECT u.memberId, u.name, u.gender, u.birthday, u.email, u.address, u.hospitalId, u.whereDidYouHear, u.expectations, u.deleted_at, h.hospitalName 
        FROM users_table u
        LEFT JOIN hospital_table h ON u.hospitalId = h.hospitalId
        WHERE u.deleted_at IS NOT NULL';
$stmt = $pdo->prepare($sql);

// SQL実行（実行に失敗すると `sql error ...` が出力される）
try {
    $status = $stmt->execute();
} catch (PDOException $e) {
    echo json_encode(["sql error" => "{$e->getMessage()}"]);
    exit();
}

$result = $stmt->fetchAll(PDO::FETCH_ASSOC); // fetchAllで全てのデータを配列で取得

// 復元処理
if (isset($_POST['restore'])) {
    // 復元対象のIDを取得
    $id = $_POST['id'];

    // 復元操作: deleted_atをNULLにリセット
    $sql = "UPDATE users_table SET deleted_at = NULL WHERE memberId = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    try {
        $status = $stmt->execute(); // 実行
    } catch (PDOException $e) {
        echo json_encode(["sql error" => "{$e->getMessage()}"]);
        exit();
    }

    // 復元後にリダイレクト
    header("Location: deleted_list.php");
    exit();
}

// SQL実行の処理を関数化
$output = "";
foreach ($result as $record) {
    $row_class = $record["deleted_at"] ? "deleted-row" : ""; // 削除済みの行には deleted-row クラスを追加

    $output .= "
    <tr>
        <td>{$record["memberId"]}</td>
        <td>{$record["name"]}</td>
        <td>{$record["gender"]}</td>
        <td>{$record["birthday"]}</td>
        <td>{$record["email"]}</td>
        <td>{$record["address"]}</td>
        <td>" . htmlspecialchars($record["hospitalName"]) . "</td> <!-- 病院名を表示 -->
        <td>{$record["whereDidYouHear"]}</td>
        <td>{$record["expectations"]}</td>
        <td>{$record["deleted_at"]}</td>
        <td>
            <!-- 復元ボタン -->
            <form method='post' action='deleted_list.php'>
                <input type='hidden' name='id' value='{$record["memberId"]}'>
                <input type='submit' name='restore' value='復元'>
            </form>
        </td>
    </tr>
    ";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>削除リスト</title>
    <link rel="stylesheet" href="./css/deleted_list.css">
</head>
<body>
    <h1>削除データリスト</h1>

    <!-- 管理者ページへのリンク -->
    <div class="admin-link">
        <a href="admin.php" class="action-btn">管理者ページ</a>
    </div>

    <table>
    <thead>
        <tr>
            <th>会員番号</th>
            <th>氏名</th>
            <th>性別</th>
            <th>誕生日</th>
            <th>メールアドレス</th>
            <th>住所</th>
            <th>所属施設</th> <!-- 病院名のカラム -->
            <th>知ったきっかけ</th>
            <th>期待する機能</th>
            <th>削除日時</th>
            <th>復元操作</th>
        </tr>
    </thead>
    <tbody>
        <?= $output ?>
    </tbody>
    </table>
</body>
</html>
