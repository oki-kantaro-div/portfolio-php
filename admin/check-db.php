<?php
/**
 * DB構造確認スクリプト（開発用）
 * このファイルをブラウザで開いてテーブル構造を確認してください
 */

require_once '../config.php';
require_once '../functions.php';

// ログインチェック（管理者のみ）
requireLogin();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB構造確認</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .table-info { margin-top: 30px; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 4px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🔍 データベース構造確認</h1>

        <?php
        global $db;

        // 1. soundsテーブルの確認
        echo '<h2 class="table-info">1. sounds テーブル</h2>';
        $sounds_check = $db->select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [DB_NAME, 'sounds']);
        if (!empty($sounds_check)) {
            echo '<div class="alert alert-success">✅ sounds テーブルは存在します</div>';
            $sounds_structure = $db->select("DESCRIBE sounds");
            echo '<table class="table table-sm table-bordered"><thead><tr><th>フィールド</th><th>型</th><th>NULL</th><th>キー</th><th>デフォルト</th><th>追加</th></tr></thead><tbody>';
            foreach ($sounds_structure as $field) {
                echo '<tr>';
                echo '<td><code>' . $field['Field'] . '</code></td>';
                echo '<td>' . $field['Type'] . '</td>';
                echo '<td>' . $field['Null'] . '</td>';
                echo '<td>' . $field['Key'] . '</td>';
                echo '<td>' . ($field['Default'] ?? 'NULL') . '</td>';
                echo '<td>' . ($field['Extra'] ?? '-') . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-danger">❌ sounds テーブルが見つかりません！</div>';
        }

        // 2. tagsテーブルの確認
        echo '<h2 class="table-info">2. tags テーブル（重要）</h2>';
        $tags_check = $db->select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [DB_NAME, 'tags']);
        if (!empty($tags_check)) {
            echo '<div class="alert alert-success">✅ tags テーブルは存在します</div>';
            $tags_structure = $db->select("DESCRIBE tags");
            echo '<table class="table table-sm table-bordered"><thead><tr><th>フィールド</th><th>型</th><th>NULL</th><th>キー</th><th>デフォルト</th><th>追加</th></tr></thead><tbody>';
            foreach ($tags_structure as $field) {
                echo '<tr>';
                echo '<td><code>' . $field['Field'] . '</code></td>';
                echo '<td>' . $field['Type'] . '</td>';
                echo '<td>' . $field['Null'] . '</td>';
                echo '<td>' . $field['Key'] . '</td>';
                echo '<td>' . ($field['Default'] ?? 'NULL') . '</td>';
                echo '<td>' . ($field['Extra'] ?? '-') . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-danger">❌ tags テーブルが見つかりません！database.sql を実行してください</div>';
        }

        // 3. 外部キー制約の確認
        echo '<h2 class="table-info">3. 外部キー制約</h2>';
        $fk_check = $db->select("SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                                 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                                 WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL", [DB_NAME]);
        if (!empty($fk_check)) {
            echo '<table class="table table-sm table-bordered"><thead><tr><th>制約名</th><th>テーブル</th><th>カラム</th><th>参照テーブル</th><th>参照カラム</th></tr></thead><tbody>';
            foreach ($fk_check as $fk) {
                echo '<tr>';
                echo '<td><code>' . $fk['CONSTRAINT_NAME'] . '</code></td>';
                echo '<td>' . $fk['TABLE_NAME'] . '</td>';
                echo '<td>' . $fk['COLUMN_NAME'] . '</td>';
                echo '<td>' . $fk['REFERENCED_TABLE_NAME'] . '</td>';
                echo '<td>' . $fk['REFERENCED_COLUMN_NAME'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning">⚠️ 外部キー制約が見つかりません</div>';
        }

        // 4. テストデータ確認
        echo '<h2 class="table-info">4. テストデータ</h2>';
        $sounds_count = $db->select("SELECT COUNT(*) as count FROM sounds");
        $tags_count = $db->select("SELECT COUNT(*) as count FROM tags");
        
        echo '<div class="alert alert-info">';
        echo '📊 sounds テーブル: ' . $sounds_count[0]['count'] . ' 件<br>';
        echo '📊 tags テーブル: ' . $tags_count[0]['count'] . ' 件';
        echo '</div>';

        // 5. 最新の音源とそのタグ
        echo '<h2 class="table-info">5. 最新の音源とタグ</h2>';
        $latest_sounds = $db->select("SELECT id, title FROM sounds ORDER BY id DESC LIMIT 3");
        if (!empty($latest_sounds)) {
            echo '<table class="table table-sm table-bordered"><thead><tr><th>ID</th><th>タイトル</th><th>タグ</th></tr></thead><tbody>';
            foreach ($latest_sounds as $sound) {
                $tags = $db->select("SELECT tag_name FROM tags WHERE sound_id = ?", [$sound['id']]);
                $tag_list = !empty($tags) ? implode(', ', array_map(function($t) { return $t['tag_name']; }, $tags)) : '（なし）';
                echo '<tr><td>' . $sound['id'] . '</td><td>' . $sound['title'] . '</td><td>' . $tag_list . '</td></tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning">⚠️ 音源データがありません</div>';
        }

        ?>

        <hr>
        <h3>📝 トラブルシューティング</h3>
        <ol>
            <li><strong>tags テーブルが見つからない場合</strong>
                <ul>
                    <li>以下の SQL を実行してください：</li>
                    <li><code>CREATE TABLE tags (id INT AUTO_INCREMENT PRIMARY KEY, sound_id INT NOT NULL, tag_name VARCHAR(50) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (sound_id) REFERENCES sounds(id) ON DELETE CASCADE, INDEX idx_sound_id (sound_id), INDEX idx_tag_name (tag_name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</code></li>
                </ul>
            </li>
            <li><strong>外部キー制約がない場合</strong>
                <ul>
                    <li>外部キーが削除されている可能性があります</li>
                    <li>tags テーブルを再作成するか、制約を追加してください</li>
                </ul>
            </li>
            <li><strong>タグが保存されない場合</strong>
                <ul>
                    <li>PHP エラーログを確認（debug コードが出力）</li>
                    <li><code>php_errors.log</code> または <code>error_log</code> を確認</li>
                </ul>
            </li>
        </ol>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">← ダッシュボードに戻る</a>
        </div>
    </div>
</body>
</html>
