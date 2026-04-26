<?php
require_once 'config.php';
require_once 'functions.php';

$page_title = 'ホーム';

// DB接続
global $db;

// 検索キーワード取得
$search = $_GET['search'] ?? '';
$search = trim($search);

// ヘッダーに渡す
$navbar_search = $search;

// ===== カテゴリーごとにグループ化された音源取得 =====
$sql_categories = "
    SELECT c.id, c.name, c.display_order 
    FROM categories c
    WHERE EXISTS (SELECT 1 FROM sounds WHERE category_id = c.id AND is_public = 1)
    ORDER BY c.display_order ASC, c.created_at ASC
";

$categories = $db->select($sql_categories);

// グループ化されたデータを構築
$grouped_sounds = [];

foreach ($categories as $category) {
    $sound_sql = 'SELECT * FROM sounds WHERE category_id = ? AND is_public = 1';
    $sound_params = [$category['id']];
    
    if (!empty($search)) {
        $sound_sql .= ' AND title LIKE ?';
        $sound_params[] = '%' . $search . '%';
    }
    
    $sound_sql .= ' ORDER BY uploaded_at DESC';
    $sounds = $db->select($sound_sql, $sound_params);
    
    if (!empty($sounds)) {
        $grouped_sounds[] = [
            'category' => $category,
            'sounds' => $sounds
        ];
    }
}

// カテゴリーなしの音源
$uncategorized_sql = 'SELECT * FROM sounds WHERE category_id IS NULL AND is_public = 1';
$uncategorized_params = [];

if (!empty($search)) {
    $uncategorized_sql .= ' AND title LIKE ?';
    $uncategorized_params[] = '%' . $search . '%';
}

$uncategorized_sql .= ' ORDER BY uploaded_at DESC';
$uncategorized = $db->select($uncategorized_sql, $uncategorized_params);

// カテゴリーがなくても音源があれば表示
if (!empty($uncategorized)) {
    $grouped_sounds[] = [
        'category' => null,
        'sounds' => $uncategorized
    ];
}

require_once 'public/header.php';
?>


<!-- メインコンテンツ -->
<div>

    <?php if (!empty($search)): ?>
        <h2 class="section-title">🔍 検索結果: "<?php echo esc($search); ?>"</h2>
    <?php endif; ?>

    <?php if (empty($grouped_sounds)): ?>
        <div class="no-data">
            <p>申し訳ありません、公開音源がありません。</p>
        </div>
    <?php else: ?>
        <?php foreach ($grouped_sounds as $group): ?>
            <!-- カテゴリーセクション -->
            <div class="category-section">
                <h2 class="category-title">
                    <?php 
                    if ($group['category']) {
                        echo '📁 ' . esc($group['category']['name']);
                    } else {
                        echo 'その他';
                    }
                    ?>
                </h2>
                
                <div class="sounds-grid">
                    <?php foreach ($group['sounds'] as $sound): ?>
                        <div class="sound-row">
                            <div class="sound-row-title">
                                <span class="title-text"><?php echo esc($sound['title']); ?></span>
                            </div>
                            <div class="sound-row-player">
                                <audio controls class="audio-player">
                                    <source src="<?php echo PUBLIC_UPLOAD_DIR . '/' . esc($sound['filename']); ?>" type="audio/mpeg">
                                </audio>
                            </div>
                            <div class="sound-row-download">
                                <a href="download.php?id=<?php echo $sound['id']; ?>" class="download-btn">
                                    DL
                                </a>
                                <div class="file-size"><?php echo formatFileSize($sound['file_size']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <p class="text-muted text-center mt-5">
            💿 全
            <?php
            $total = 0;
            foreach ($grouped_sounds as $group) {
                $total += count($group['sounds']);
            }
            echo '<strong>' . $total . '</strong>';
            ?>
            件の音源を配信中
        </p>
    <?php endif; ?>
</div>