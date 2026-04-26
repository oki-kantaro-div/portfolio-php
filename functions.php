<?php
/**
 * 共通関数・クラス群
 */

// ===== Database クラス =====
class Database {
    private $pdo;
    private $error;

    public function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die('【エラー】データベース接続失敗: ' . htmlspecialchars($this->error));
        }
    }

    /**
     * SELECT クエリ実行
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function select($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return [];
        }
    }

    /**
     * 単一行取得
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public function selectOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }

    /**
     * INSERT/UPDATE/DELETE実行
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 最後に挿入されたIDを取得
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * エラー取得
     * @return string
     */
    public function getError() {
        return $this->error;
    }
}

// ===== グローバルDB接続インスタンス =====
$db = new Database();

// ===== ユーティリティ関数 =====

/**
 * ログインチェック（管理画面用）
 * ログインしていない場合はログインページへリダイレクト
 */
function requireLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . ADMIN_URL . '/');
        exit;
    }
}

/**
 * CSRF トークン生成・検証
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * エスケープ関数
 * @param string $value
 * @return string
 */
function esc($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * ファイル検証
 * @param array $file $_FILES の要素
 * @return array ['valid' => bool, 'error' => string]
 */
function validateAudioFile($file) {
    $result = ['valid' => false, 'error' => ''];

    // ファイルがアップロードされたか
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $result['error'] = 'ファイルが選択されていません。';
        return $result;
    }

    // ファイルサイズチェック
    if ($file['size'] > MAX_FILE_SIZE) {
        $result['error'] = 'ファイルサイズは10MB以下にしてください。';
        return $result;
    }

    // 拡張子チェック
    $pathinfo = pathinfo($file['name']);
    $ext = strtolower($pathinfo['extension'] ?? '');
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        $result['error'] = 'mp3ファイルのみアップロード可能です。';
        return $result;
    }

    // MIMEタイプチェック
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_MIME_TYPES)) {
        $result['error'] = '有効なMP3ファイルではありません。';
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * ランダムなファイル名を生成
 * @return string
 */
function generateRandomFileName() {
    return 'audio_' . bin2hex(random_bytes(16)) . '.mp3';
}

/**
 * ファイルをアップロード
 * @param array $file $_FILES の要素
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadAudioFile($file) {
    $result = ['success' => false, 'filename' => '', 'error' => ''];

    // ファイル検証
    $validation = validateAudioFile($file);
    if (!$validation['valid']) {
        $result['error'] = $validation['error'];
        return $result;
    }

    // ディレクトリ作成確認
    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            $result['error'] = 'ファイル保存ディレクトリの作成に失敗しました。';
            return $result;
        }
    }

    // ファイル名生成・保存
    $newFilename = generateRandomFileName();
    $targetPath = UPLOAD_DIR . '/' . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $result['success'] = true;
        $result['filename'] = $newFilename;
    } else {
        $result['error'] = 'ファイルの保存に失敗しました。';
    }

    return $result;
}

/**
 * ファイルを物理削除
 * @param string $filename
 * @return bool
 */
function deleteAudioFile($filename) {
    $filePath = UPLOAD_DIR . '/' . basename($filename);
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return true; // ファイルなくても成功とする
}

/**
 * ファイルサイズを人間が読みやすい形式に変換
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 日時をフォーマット
 * @param string $datetime
 * @return string
 */
function formatDateTime($datetime) {
    return date('Y年m月d日 H:i', strtotime($datetime));
}

/**
 * リダイレクト
 * @param string $url
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * 成功メッセージをセッションに保存
 * @param string $message
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * エラーメッセージをセッションに保存
 * @param string $message
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * セッションメッセージを取得・削除
 */
function getSessionMessage($type = 'success') {
    $key = $type . '_message';
    $message = $_SESSION[$key] ?? null;
    if ($message) {
        unset($_SESSION[$key]);
    }
    return $message;
}
