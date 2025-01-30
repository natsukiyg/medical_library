<?php

// db_config.phpからデータベース接続情報を持ってくる
include("db_config.php"); // db_config.phpの中身を読み込むので、$dbnや$pdoが使えるようになる
// セッション開始
session_start();
//$pdo->exec("USE {$dbName}"); // データベースを選択


// POSTデータ確認 ()内にダメな条件を書く
if(!isset($_POST["name"]) || $_POST["name"] === "" ||
   !isset($_POST["gender"]) || $_POST["gender"] === "" ||
   !isset($_POST["birthday"]) || $_POST["birthday"] === "" ||
   !isset($_POST["email"]) || $_POST["email"] === "" ||
   !isset($_POST["password"]) || $_POST["password"] === "" ||
   !isset($_POST["address"]) || $_POST["address"] === "" ||
   !isset($_POST["hospitalName"]) || $_POST["hospitalName"] === "" ||
   !isset($_POST["user_role"]) || $_POST["user_role"] === "" ||
   !isset($_POST["whereDidYouHear"]) || $_POST["whereDidYouHear"] === "" ||
   !isset($_POST["expectations"]) || $_POST["expectations"] === "" ) { // いずれかが空の場合
    exit("データがありません");
}

// POSTデータ取得
$name = $_POST["name"];
$gender = $_POST["gender"];
$birthday = $_POST["birthday"];
$email = $_POST["email"];
$password = $_POST["password"];
$address = $_POST["address"];
$hospitalName = $_POST["hospitalName"];
$user_role = $_POST["user_role"];
$whereDidYouHear = $_POST["whereDidYouHear"];
$expectations = $_POST["expectations"];

//バリデーション：メールアドレスの形式チェック
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit("無効なメールアドレスです");
}

//バリデーション：パスワードの形式チェック
if (!preg_match("/^(?=.*[a-zA-Z])(?=.*\d)[A-Za-z\d]{8,30}$/", $password)) {
  exit("パスワードは英数字を含めて8〜30文字で入力してください");
}

//バリデーション：誕生日の形式チェック
if (!strtotime($birthday)) {
  exit("誕生日を正しい形式で入力してください");
}

//パスワードをハッシュ化
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

//もしパスワードのハッシュ化が失敗した場合、エラーメッセージを出す
if (!$passwordHash) {
  exit("パスワードのハッシュ化に失敗しました");
}

//user_roleが1または2の場合、承認待ちとして登録
$is_approved = ($user_role === 0) ? 1 : 0; // 0:承認済み, 1:承認待ち

//hospitalNameがhospital_tableに存在するか確認
$stmt = $pdo->prepare('SELECT hospitalId FROM hospital_table WHERE hospitalName=:hospitalName');
$stmt->bindValue(':hospitalName', $hospitalName, PDO::PARAM_STR);
$stmt->execute();
$hospital = $stmt->fetch(PDO::FETCH_ASSOC);

//hospitalNameが存在しない場合、hospital_tableに新規登録
if (!$hospital) {
  $stmt = $pdo->prepare('INSERT INTO hospital_table (hospitalName) VALUES (:hospitalName)');
  $stmt->bindValue(':hospitalName', $hospitalName, PDO::PARAM_STR);
  $stmt->execute();
  //新しく登録されたhospitalIdを取得
  $hospitalId = $pdo->lastInsertId(); 
} else {
  //既に存在する場合、hospitalIdを取得
  $hospitalId = $hospital['hospitalId'];
}

// SQL作成&実行
$sql = 'INSERT INTO users_table (memberId, name, gender, birthday, email, password, address, hospitalId, user_role, is_approved, whereDidYouHear, expectations, registered_at, updated_at)
        VALUES (NULL, :name, :gender, :birthday, :email, :password, :address, :hospitalId, :user_role, :is_approved, :whereDidYouHear, :expectations, now(), now())';
//SQLインジェクションを起こさないために「:name」や「:gender」（バインド変数）を使っているところ
$stmt = $pdo->prepare($sql);

// バインド変数を設定
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
$stmt->bindValue(':birthday', $birthday, PDO::PARAM_STR);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);
$stmt->bindValue(':address', $address, PDO::PARAM_STR);
$stmt->bindValue(':hospitalId', $hospitalId, PDO::PARAM_INT);
$stmt->bindValue(':user_role', $user_role, PDO::PARAM_INT);
$stmt->bindValue(':is_approved', $is_approved, PDO::PARAM_INT); // 承認待ちの場合は1、それ以外は0
$stmt->bindValue(':whereDidYouHear', $whereDidYouHear, PDO::PARAM_STR);
$stmt->bindValue(':expectations', $expectations, PDO::PARAM_STR);

// SQL実行（実行に失敗すると `sql error ...` が出力される）
try {
  $status = $stmt->execute(); //実行ボタンを押して、うまくいったかいってないかを判断する
} catch (PDOException $e) {
  echo json_encode(["sql error" => "{$e->getMessage()}"]);
  exit();
}

// 登録データをSESSIONに保存(GETパラメータよりセキュリティが高いSESSIONを使う)
$_SESSION['member_data'] = [
  'name' => $name,
  'gender' => $gender,
  'birthday' => $birthday,
  'email' => $email,
  'password' => $password,
  'address' => $address,
  'hospitalName' => $hospitalName,
  'user_role' => $user_role,
  'is_approved' => $is_approved,
  'whereDidYouHear' => $whereDidYouHear,
  'expectations' => $expectations
];

//データ挿入後、確認ページへリダイレクト
header("Location: confirmation.php");
exit(); // これ以下の処理を行わない