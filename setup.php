<?php
/**
 * 初期セットアップスクリプト
 * 管理者ユーザーのハッシュ化されたパスワードを生成します
 * 
 * 使い方：
 * 1. このファイルをブラウザで開く
 * 2. 表示されたハッシュをコピー
 * 3. database.sql の INSERT INTO admin_users 文に貼り付け
 * 4. database.sql を実行
 */

// 設定
$username = 'admin';
$password = 'admin123';  // ← 初期パスワード（後で変更してください）

// ハッシュ化
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>初期セットアップ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px 20px;
        }
        .setup-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .code-block {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
            font-family: monospace;
            word-break: break-all;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1 class="mb-4">🎵 初期セットアップ</h1>

        <div class="alert alert-info" role="alert">
            <strong>⚠️ 重要：</strong> このページは開発時のみです。本番環境では削除してください。
        </div>

        <h3>1. データベース作成</h3>
        <p class="text-muted">以下の SQL をMyPHPAdmin のコンソールで実行してください：</p>
        <div class="code-block">
DROP DATABASE IF EXISTS audio_download;
CREATE DATABASE audio_download DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE audio_download;

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sounds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL UNIQUE,
  original_name VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  is_public TINYINT(1) DEFAULT 1,
  file_size INT NOT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  download_count INT DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_is_public (is_public),
  INDEX idx_uploaded_at (uploaded_at),
  INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        </div>

        <h3>2. 初期管理者ユーザー作成</h3>
        <p class="text-muted">以下の SQL を実行してください：</p>
        
        <div class="code-block">
INSERT INTO admin_users (username, password) 
VALUES ('<?php echo $username; ?>', '<?php echo $hashed_password; ?>');
        </div>

        <div class="alert alert-success mt-3" role="alert">
            <strong>✅ 完成！</strong>
            <ul class="mb-0 mt-2">
                <li>ユーザーID: <code><?php echo $username; ?></code></li>
                <li>初期パスワード: <code><?php echo $password; ?></code></li>
            </ul>
            <p class="mt-2 mb-0 small">⚠️ <strong>初回ログイン後、パスワードを変更してください。</strong></p>
        </div>

        <h3>3. 次のステップ</h3>
        <ol>
            <li>MyPHPAdmin で上記 SQL を実行</li>
            <li>XAMPPで <code>http://localhost/portfolio-php/admin/</code> にアクセス</li>
            <li>上記のユーザーIDとパスワードでログイン</li>
            <li>このセットアップファイルは削除</li>
        </ol>

        <hr>

        <h3>トラブルシューティング</h3>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        DB接続エラーが出る場合
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p><strong>確認事項：</strong></p>
                        <ul>
                            <li>XAMPPでMySQL が起動しているか</li>
                            <li><code>config.php</code> の DB_HOST, DB_USER, DB_PASS が正しいか</li>
                            <li>データベース名が <code>audio_download</code> になっているか</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        ファイルアップロードができない場合
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p><strong>確認事項：</strong></p>
                        <ul>
                            <li><code>uploads/audio/</code> フォルダが存在するか</li>
                            <li>フォルダの書き込み権限があるか（755 以上）</li>
                            <li><code>php.ini</code> の <code>upload_max_filesize</code> が 10MB 以上に設定されているか</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
