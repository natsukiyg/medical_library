<?php

// Composer でインストールした dotenv を読み込む
require 'vendor/autoload.php';

// .env ファイルを読み込む
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

/* // .env ファイルの読み込み確認
echo '.env ファイルの読み込みに成功しました！<br>';

// 環境変数の値をデバッグ表示
echo 'DB_NAME_LOCAL: ' . $_ENV['DB_NAME_LOCAL'] . '<br>';
echo 'DB_HOST_LOCAL: ' . $_ENV['DB_HOST_LOCAL'] . '<br>';
echo 'DB_PORT_LOCAL: ' . $_ENV['DB_PORT_LOCAL'] . '<br>';
 */

// 環境に応じて接続設定を切り替え
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // localhost環境
    $dbHost = $_ENV['DB_HOST_LOCAL'];
    $dbPort = $_ENV['DB_PORT_LOCAL'];
    $dbName = $_ENV['DB_NAME_LOCAL'];
    $dbUser = $_ENV['DB_USER_LOCAL'];
    $dbPass = $_ENV['DB_PASS_LOCAL'] ?: ''; // パスワードが設定されていない場合は空文字
} else {
    // 本番環境
    $dbHost = $_ENV['DB_HOST_PROD'];
    $dbPort = $_ENV['DB_PORT_PROD'];
    $dbName = $_ENV['DB_NAME_PROD'];
    $dbUser = $_ENV['DB_USER_PROD'];
    $dbPass = $_ENV['DB_PASS_PROD'];
}

// データベース接続
try {
    // DSN (Data Source Name) の作成
    $dsn = "mysql:dbname={$dbName};charset=utf8mb4;port={$dbPort};host={$dbHost}";
    
    // デバッグ: DSN と環境変数を表示
    //echo '接続する DSN: ' . $dsn . '<br>';
    //echo '接続するデータベース名: ' . $dbName . '<br>';
    
    // PDOインスタンスの作成
    $pdo = new PDO($dsn, $dbUser, $dbPass);

    // エラーモードを例外に設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 接続成功メッセージ
    //echo "データベース接続成功！<br>";
    //echo "使用するデータベース名: " . $dbName . "<br>";
    
} catch (PDOException $e) {
    // 接続エラーの場合
    echo "データベース接続エラー: " . $e->getMessage();
    exit();
}
