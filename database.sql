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
  category_id INT COMMENT '小カテゴリID（sub_categories.id）',
  is_public TINYINT(1) DEFAULT 1 COMMENT '1=公開, 0=非公開',
  file_size INT NOT NULL COMMENT 'バイト単位',
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  download_count INT DEFAULT 0 COMMENT '将来用：ダウンロード数',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_is_public (is_public),
  INDEX idx_uploaded_at (uploaded_at),
  INDEX idx_title (title),
  INDEX idx_category_id (category_id),
  FOREIGN KEY (category_id) REFERENCES sub_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- タグテーブル
CREATE TABLE IF NOT EXISTS tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sound_id INT NOT NULL,
  tag_name VARCHAR(50) NOT NULL COMMENT 'タグ名',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sound_id) REFERENCES sounds(id) ON DELETE CASCADE,
  INDEX idx_sound_id (sound_id),
  INDEX idx_tag_name (tag_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 大カテゴリテーブル
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL COMMENT '大カテゴリ名',
  display_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_name (name),
  INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 小カテゴリテーブル
CREATE TABLE IF NOT EXISTS sub_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT NOT NULL COMMENT '大カテゴリID（categories.id）',
  name VARCHAR(100) NOT NULL COMMENT '小カテゴリ名',
  display_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE,
  UNIQUE KEY unique_parent_name (parent_id, name),
  INDEX idx_parent_id (parent_id),
  INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- お知らせテーブル
-- 公開開始日〜公開終了日の範囲で公開状態を判定する
CREATE TABLE IF NOT EXISTS NewsRelease (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL COMMENT 'お知らせタイトル',
  body TEXT NOT NULL COMMENT 'お知らせ本文',
  is_important TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=重要',
  publish_start DATETIME NOT NULL COMMENT '公開開始日',
  publish_end DATETIME NULL COMMENT '公開終了日（NULLなら終了なし）',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_publish_start (publish_start),
  INDEX idx_publish_end (publish_end),
  INDEX idx_is_important (is_important)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- おすすめレコード管理テーブル
CREATE TABLE IF NOT EXISTS featured_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sound_id INT NOT NULL UNIQUE COMMENT 'おすすめレコード対象の音源ID',
  display_order INT DEFAULT 0 COMMENT '表示順序',
  featured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '追加日時',
  FOREIGN KEY (sound_id) REFERENCES sounds(id) ON DELETE CASCADE,
  INDEX idx_display_order (display_order)
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
