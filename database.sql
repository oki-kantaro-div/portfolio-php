-- データベース作成スクリプト
-- このスクリプトをMyPHPAdminやコマンドラインで実行してください

-- データベース作成
CREATE DATABASE IF NOT EXISTS audio_download DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE audio_download;

-- 管理者ユーザーテーブル
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 音源テーブル
CREATE TABLE IF NOT EXISTS sounds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL UNIQUE COMMENT 'サーバー保存時のランダムなファイル名',
  original_name VARCHAR(255) NOT NULL COMMENT '元のアップロードファイル名',
  title VARCHAR(255) NOT NULL,
  description TEXT,
  is_public TINYINT(1) DEFAULT 1 COMMENT '1=公開, 0=非公開',
  file_size INT NOT NULL COMMENT 'バイト単位',
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  download_count INT DEFAULT 0 COMMENT '将来用：ダウンロード数',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_is_public (is_public),
  INDEX idx_uploaded_at (uploaded_at),
  INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 初期管理者ユーザー作成（例）
-- ユーザーID: admin
-- パスワード: admin123

INSERT INTO admin_users (username, password) 
VALUES ('admin', '$2y$10$YmFzZTY0X2VuY29kZWQ=');

-- 上のパスワードはプレースホルダーです。以下のPHPコードで正しいハッシュを生成してください：
-- echo password_hash('admin123', PASSWORD_DEFAULT);

-- 実際のハッシュに置き換えて以下を実行してください：
-- DELETE FROM admin_users;
-- INSERT INTO admin_users (username, password) VALUES ('admin', '[上で生成されたハッシュ]');
