<?php
session_start(); // セッション開始

// ログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ユーザー情報を取得
$user_id = $_SESSION['user_id']; // ユーザーID
$user_name = $_SESSION['user_name']; // ユーザー名

// データベース接続
include("db_config.php");

// 病院名と役職・権限の登録処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 病院名と役職名が送信されている場合
    $hospital_name = trim($_POST['hospital_name']);
    $department_name = trim($_POST['department_name']);
    $user_role = $_POST['user_role'];

    // 病院名、部署名、役職が全て入力されている場合
    if (!empty($hospital_name) && !empty($department_name) && !empty($user_role)) {
        // 病院名が存在するか確認
        $stmt = $pdo->prepare("SELECT hospitalId FROM hospital_table WHERE hospitalName = :hospitalName");
        $stmt->bindParam(':hospitalName', $hospital_name, PDO::PARAM_STR);
        $stmt->execute();
        $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($hospital) {
            $hospitalId = $hospital['hospitalId'];
        } else {
            // 新規の場合、hospital_tableに病院名を追加
            $stmt = $pdo->prepare("INSERT INTO hospital_table (hospitalName) VALUES (:hospitalName)");
            $stmt->bindParam(':hospitalName', $hospital_name, PDO::PARAM_STR);
            $stmt->execute();
            $hospitalId = $pdo->lastInsertId();
        }

        // 部署名が存在するか確認
        $stmt = $pdo->prepare("SELECT departmentId FROM department_table WHERE hospitalId = :hospitalId AND departmentName = :departmentName");
        $stmt->bindParam(':hospitalId', $hospitalId, PDO::PARAM_INT);
        $stmt->bindParam(':departmentName', $department_name, PDO::PARAM_STR);
        $stmt->execute();
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($department) {
            $departmentId = $department['departmentId'];
        } else {
            // 新規の場合、department_tableに部署名を追加
            $stmt = $pdo->prepare("INSERT INTO department_table (hospitalId, departmentName) VALUES (:hospitalId, :departmentName)");
            $stmt->bindParam(':hospitalId', $hospitalId, PDO::PARAM_INT);
            $stmt->bindParam(':departmentName', $department_name, PDO::PARAM_STR);
            $stmt->execute();
            $departmentId = $pdo->lastInsertId();
        }

        // user_hospital_tableに登録
        $stmt = $pdo->prepare("INSERT INTO user_hospital_table (memberId, hospitalId, departmentId, approval_status, registered_at, updated_at) 
                               VALUES (:memberId, :hospitalId, :departmentId, 'pending', NOW(), NOW())");
        $stmt->bindParam(':memberId', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':hospitalId', $hospitalId, PDO::PARAM_INT);
        $stmt->bindParam(':departmentId', $departmentId, PDO::PARAM_INT);
        $stmt->execute();

        // user_role_tableに登録
        $stmt = $pdo->prepare("INSERT INTO user_role_table (memberId, hospitalId, user_role, approval_status, registered_at, updated_at)
                               VALUES (:memberId, :hospitalId, :user_role, 'pending', NOW(), NOW())");
        $stmt->bindParam(':memberId', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':hospitalId', $hospitalId, PDO::PARAM_INT);
        $stmt->bindParam(':user_role', $user_role, PDO::PARAM_INT);
        $stmt->execute();

        // セッションに病院情報と承認ステータスを保存
        $_SESSION['hospitalName'] = $hospital_name;
        $_SESSION['departmentName'] = $department_name;
        $_SESSION['user_role'] = $user_role;
        $_SESSION['approval_status'] = 'pending';

        // 登録完了メッセージとダッシュボードにリダイレクト
        header('Location: user_dashboard.php');
        exit;
    } else {
        $error_message = "病院名、部署名、役職は全て入力してください。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>所属施設/権限登録</title>
    <link rel="stylesheet" href="./css/register_hospital_role.css">
</head>
<body>

<h1>所属施設/権限登録フォーム</h1>

<?php if (isset($error_message)) : ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>

<form method="POST" action="register_hospital_role.php">
    <label for="hospital_name">所属施設名</label>
    <input type="text" id="hospital_name" name="hospital_name" placeholder="病院名を入力してください" required>
    
    <!-- オートコンプリート候補表示 -->
    <div id="hospital-suggestions"></div>

    <label for="department_name">所属部署名</label>
    <input type="text" id="department_name" name="department_name" placeholder="部署名を入力してください" required>

    <label for="user_role">権限</label>
    <select name="user_role" id="user_role" required>
        <option value="0">スタッフ（閲覧のみ）</option>
        <option value="1">チームメンバー（編集可能）</option>
        <option value="2">管理者</option>
    </select>

    <button type="submit">登録</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // 病院名の入力に合わせて予測変換を表示
    $('#hospital_name').on('input', function() {
        var searchQuery = $(this).val();
        if (searchQuery.length > 2) { // 3文字以上で検索
            $.get('register_hospital_role.php', { search: searchQuery }, function(data) {
                var suggestions = JSON.parse(data);
                var suggestionsHtml = '';
                
                if (suggestions.length > 0) {
                    suggestions.forEach(function(hospital) {
                        suggestionsHtml += '<div>' + hospital.hospitalName + '</div>';
                    });
                    $('#hospital-suggestions').html(suggestionsHtml).show();
                } else {
                    $('#hospital-suggestions').hide();
                }
            });
        } else {
            $('#hospital-suggestions').hide();
        }
    });

    // 候補をクリックしたときに病院名を入力フィールドに設定
    $(document).on('click', '#hospital-suggestions div', function() {
        $('#hospital_name').val($(this).text());
        $('#hospital-suggestions').hide();
    });
</script>

</body>
</html>
