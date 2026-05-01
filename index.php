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
$navbar_categories = [];

// ===== カテゴリーごとにグループ化された音源取得 =====
$sql_categories = "
    SELECT c.id, c.name, c.display_order 
    FROM categories c
    WHERE EXISTS (SELECT 1 FROM sounds WHERE category_id = c.id AND is_public = 1)
    ORDER BY c.display_order ASC, c.created_at ASC
";

$categories = $db->select($sql_categories);
$navbar_categories = $categories;

// グループ化されたデータを構築
$grouped_sounds = [];

foreach ($categories as $category) {
    $sound_sql = 'SELECT * FROM sounds WHERE category_id = ? AND is_public = 1';
    $sound_params = [$category['id']];

    if (!empty($search)) {
        $sound_sql .= ' AND (title LIKE ? OR description LIKE ? OR id IN (SELECT sound_id FROM tags WHERE tag_name LIKE ?))';
        $search_param = '%' . $search . '%';
        $sound_params[] = $search_param;
        $sound_params[] = $search_param;
        $sound_params[] = $search_param;
    }

    $sound_sql .= ' ORDER BY uploaded_at DESC';
    $sounds = $db->select($sound_sql, $sound_params);

    // 各音源にタグを付加
    foreach ($sounds as &$sound) {
        $tags = $db->select('SELECT tag_name FROM tags WHERE sound_id = ? ORDER BY created_at ASC', [$sound['id']]);
        $sound['tags'] = $tags;
    }

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
    $uncategorized_sql .= ' AND (title LIKE ? OR description LIKE ? OR id IN (SELECT sound_id FROM tags WHERE tag_name LIKE ?))';
    $search_param = '%' . $search . '%';
    $uncategorized_params[] = $search_param;
    $uncategorized_params[] = $search_param;
    $uncategorized_params[] = $search_param;
}

$uncategorized_sql .= ' ORDER BY uploaded_at DESC';
$uncategorized = $db->select($uncategorized_sql, $uncategorized_params);

// 各音源にタグを付加
foreach ($uncategorized as &$sound) {
    $tags = $db->select('SELECT tag_name FROM tags WHERE sound_id = ? ORDER BY created_at ASC', [$sound['id']]);
    $sound['tags'] = $tags;
}

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
            <?php
            $section_id = $group['category']
                ? ('category-' . $group['category']['id'])
                : 'category-uncategorized';
            ?>
            <div class="category-section" id="<?php echo esc($section_id); ?>">
                <h2 class="category-title">
                    <?php
                    if ($group['category']) {
                        echo '　' . esc($group['category']['name']);
                    } else {
                        echo '　その他';
                    }
                    ?>
                </h2>

                <div class="sounds-grid">
                    <?php foreach ($group['sounds'] as $sound): ?>
                        <div class="sound-row">
                            <!-- 左30% : タイトル -->
                            <div class="sound-row-title">
                                <span class="title-text"><?php echo esc($sound['title']); ?></span>
                            </div>

                            <!-- 中40% : タグと説明 -->
                            <div class="sound-row-middle">
                                <!-- タグ表示 -->
                                <?php if (!empty($sound['tags'])): ?>
                                    <div class="tags-section">
                                        <?php foreach ($sound['tags'] as $tag): ?>
                                            <span class="tag-badge">#<?php echo esc($tag['tag_name']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- 説明表示 -->
                                <?php if (!empty($sound['description'])): ?>
                                    <div class="description-section">
                                        <?php echo esc($sound['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- 右30% : 試聴とDL -->
                            <div class="sound-row-controls">
                                <div class="sound-row-player">
                                    <div class="custom-player">
                                        <button class="play-btn">▶</button>
                                        <div class="progress-container">
                                            <div class="progress-bar-wrapper">
                                                <div class="progress-bar"></div>
                                            </div>
                                            <div class="time-display">0:00 / 0:00</div>
                                        </div>
                                        <div class="volume-control">
                                            <span class="volume-icon">🔊</span>
                                            <input type="range" class="volume-slider" min="0" max="100" value="80">
                                        </div>
                                        <audio class="audio-player">
                                            <source src="<?php echo PUBLIC_UPLOAD_DIR . '/' . esc($sound['filename']); ?>" type="audio/mpeg">
                                        </audio>
                                    </div>
                                </div>
                                <div class="sound-row-download">
                                    <a href="download.php?id=<?php echo $sound['id']; ?>" class="download-btn">
                                        ダウンロード
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// TOPページ：ロード後5秒間操作がなければ自動スクロール（1回だけ判定）
document.addEventListener('DOMContentLoaded', () => {
    const IDLE_MS = 5000;
    const SCROLL_STEP_PX = 1;      // 1tickあたりのスクロール量
    const SCROLL_INTERVAL_MS = 16; // 約60fps

    let idleTimer = null;
    let scrollTimer = null;
    let disabledForThisPage = false;

    const atBottom = () =>
        (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 2);

    const stopAutoScroll = () => {
        if (scrollTimer) {
            clearInterval(scrollTimer);
            scrollTimer = null;
        }
    };

    const startAutoScroll = () => {
        if (disabledForThisPage) return;
        if (scrollTimer) return;
        scrollTimer = setInterval(() => {
            if (document.hidden || atBottom()) {
                stopAutoScroll();
                return;
            }
            window.scrollBy(0, SCROLL_STEP_PX);
        }, SCROLL_INTERVAL_MS);
    };

    const armOneShotIdleCheck = () => {
        if (idleTimer) clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            if (!document.hidden && !disabledForThisPage) startAutoScroll();
        }, IDLE_MS);
    };

    const onUserActivity = () => {
        stopAutoScroll();
        disabledForThisPage = true;
        if (idleTimer) {
            clearTimeout(idleTimer);
            idleTimer = null;
        }
    };

    // 初回：ロード後に待機→自動スクロール
    armOneShotIdleCheck();

    // ユーザー操作が1回でもあれば、そのページ滞在中は無効化（再開しない）
    const activityEvents = [
        'pointerdown',
        'mousedown',
        'touchstart',
        'wheel',
        'scroll',
        'keydown'
    ];
    activityEvents.forEach(evt => window.addEventListener(evt, onUserActivity, { passive: true }));

    // タブ非表示中は止める（戻っても再アームしない）
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoScroll();
        }
    });
});
</script>