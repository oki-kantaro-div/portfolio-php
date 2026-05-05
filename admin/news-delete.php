<?php
require_once '../config.php';
require_once '../functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . '/news.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setErrorMessage('セキュリティトークンが無効です。');
    redirect(ADMIN_URL . '/news.php');
}

$id = $_POST['id'] ?? null;
if (empty($id) || !is_numeric($id)) {
    setErrorMessage('無効なリクエストです。');
    redirect(ADMIN_URL . '/news.php');
}
$id = (int)$id;

global $db;

$news = $db->selectOne('SELECT * FROM NewsRelease WHERE id = ?', [$id]);
if (!$news) {
    setErrorMessage('お知らせが見つかりません。');
    redirect(ADMIN_URL . '/news.php');
}

if ($db->execute('DELETE FROM NewsRelease WHERE id = ?', [$id])) {
    setSuccessMessage('「' . $news['title'] . '」を削除しました。');
} else {
    setErrorMessage('削除に失敗しました。');
}

redirect(ADMIN_URL . '/news.php');

