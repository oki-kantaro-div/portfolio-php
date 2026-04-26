<?php
require_once '../config.php';
require_once '../functions.php';

// ログインチェック
requireLogin();

// POSTリクエスト確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . '/dashboard.php');
}

// CSRFトークン検証
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setErrorMessage('セキュリティトークンが無効です。');
    redirect(ADMIN_URL . '/dashboard.php');
}

// IDパラメータ取得
$id = $_POST['id'] ?? null;
if (empty($id) || !is_numeric($id)) {
    setErrorMessage('無効なリクエストです。');
    redirect(ADMIN_URL . '/dashboard.php');
}

// DB接続
global $db;

// 音源データ取得
$sound = $db->selectOne('SELECT * FROM sounds WHERE id = ?', [$id]);
if (!$sound) {
    setErrorMessage('音源が見つかりません。');
    redirect(ADMIN_URL . '/dashboard.php');
}

// ファイルを물理削除
deleteAudioFile($sound['filename']);

// DBから削除
$sql = 'DELETE FROM sounds WHERE id = ?';
if ($db->execute($sql, [$id])) {
    setSuccessMessage('「' . $sound['title'] . '」を削除しました。');
} else {
    setErrorMessage('削除に失敗しました。');
}

redirect(ADMIN_URL . '/dashboard.php');
