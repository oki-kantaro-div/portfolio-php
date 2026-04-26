<?php
require_once '../config.php';
require_once '../functions.php';

// POSTリクエストの確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . '/');
}

// CSRFトークン検証
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setErrorMessage('セキュリティトークンが無効です。もう一度ログインしてください。');
    redirect(ADMIN_URL . '/');
}

// ユーザー入力の取得
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 入力値チェック
if (empty($username) || empty($password)) {
    setErrorMessage('ユーザーIDとパスワードを入力してください。');
    redirect(ADMIN_URL . '/');
}

// DB接続
global $db;

// ユーザーをDBから検索
$user = $db->selectOne(
    'SELECT id, username, password FROM admin_users WHERE username = ?',
    [$username]
);

// ユーザーが存在し、パスワードが一致するか確認
if ($user && password_verify($password, $user['password'])) {
    // ログイン成功：セッションに保存
    $_SESSION['admin_id'] = $user['username'];
    $_SESSION['login_time'] = time();
    
    setSuccessMessage('ログインしました。');
    redirect(ADMIN_URL . '/dashboard.php');
} else {
    // ログイン失敗
    setErrorMessage('ユーザーIDまたはパスワードが間違っています。');
    redirect(ADMIN_URL . '/');
}
