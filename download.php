<?php
require_once 'config.php';
require_once 'functions.php';

// IDパラメータ取得
$id = $_GET['id'] ?? null;
if (empty($id) || !is_numeric($id)) {
    http_response_code(400);
    die('無効なリクエストです。');
}

// DB接続
global $db;

// 音源データ取得（公開中のみ）
$sound = $db->selectOne(
    'SELECT * FROM sounds WHERE id = ? AND is_public = 1',
    [$id]
);

if (!$sound) {
    http_response_code(404);
    die('音源が見つかりません。');
}

// ファイルパス
$filePath = UPLOAD_DIR . '/' . $sound['filename'];

// ファイル存在確認
if (!file_exists($filePath)) {
    http_response_code(404);
    die('ファイルが見つかりません。');
}

// ダウンロード数更新（任意）
$db->execute('UPDATE sounds SET download_count = download_count + 1 WHERE id = ?', [$id]);

// ダウンロード処理
header('Content-Type: audio/mpeg');
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: attachment; filename="' . $sound['original_name'] . '"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// ファイル出力
readfile($filePath);
exit;
