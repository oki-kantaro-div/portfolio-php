<?php
/**
 * カテゴリー機能のマイグレーション
 * database.sqlの実行後に、このファイルをブラウザから実行してください
 * http://localhost/portfolio-php/migrate_categories.php
 */

require_once 'config.php';
require_once 'functions.php';

global $db;

try {
    // 1. カテゴリーテーブル作成
    $create_categories_sql = "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE COMMENT 'カテゴリー名（例：環境音①）',
            display_order INT DEFAULT 0 COMMENT '表示順序',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_display_order (display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->execute($create_categories_sql);
    echo "✓ categoriesテーブルを作成しました<br>";
    
    // 2. soundsテーブルにcategory_idカラムを追加
    $check_column = "
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME='sounds' AND COLUMN_NAME='category_id' AND TABLE_SCHEMA=DATABASE()
    ";
    
    $result = $db->select($check_column);
    
    if (empty($result)) {
        $alter_sounds_sql = "
            ALTER TABLE sounds 
            ADD COLUMN category_id INT DEFAULT NULL COMMENT 'カテゴリーID',
            ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ";
        
        $db->execute($alter_sounds_sql);
        echo "✓ soundsテーブルにcategory_idカラムを追加しました<br>";
    } else {
        echo "✓ category_idカラムは既に存在します<br>";
    }
    
    echo "<br><strong>マイグレーション完了！</strong><br>";
    echo '<a href="admin/index.php">管理画面に戻る</a>';
    
} catch (Exception $e) {
    echo "エラーが発生しました: " . $e->getMessage();
}
?>
