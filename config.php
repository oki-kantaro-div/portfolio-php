<?php
/**
 * 設定ファイル - DB接続、定数、パス管理
 */

// エラー表示は開発時のみ（本番環境ではログに出力）
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('default_charset', 'UTF-8');

// ===== データベース設定 =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'audio_download');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPPのデフォルトはブランク

// ===== アプリケーション定数 =====
define('APP_NAME', 'オトリウム');
define('APP_ROOT', dirname(__FILE__));
define('ADMIN_ROOT', APP_ROOT . '/admin');
define('UPLOAD_DIR', APP_ROOT . '/uploads/audio');
define('PUBLIC_UPLOAD_DIR', '/uploads/audio'); // URL用相対パス

// ===== ファイルアップロード制限 =====
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['mp3']);
define('ALLOWED_MIME_TYPES', ['audio/mpeg']);

// ===== セッション設定 =====
define('SESSION_TIMEOUT', 3600 * 2); // 2時間
define('SESSION_NAME', 'audio_admin_session');

// ===== URL定数 =====
// 現在のアクセス方法に基づいてプロトコルとホストを動的に取得
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080'; // ポート番号を含む
define('SITE_URL', $protocol . $host . '/portfolio-php');
define('ADMIN_URL', SITE_URL . '/admin');

// ===== セッション開始（毎回読み込まれるため） =====
session_name(SESSION_NAME);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ===== セッションタイムアウト確認 =====
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        // タイムアウト：セッション破棄
        session_destroy();
        $_SESSION = [];
    }
}
$_SESSION['last_activity'] = time();

// ===== Timezone設定 =====
date_default_timezone_set('Asia/Tokyo');

// ===== グローバル関数（DB接続）=====
// 詳細は functions.php で定義
