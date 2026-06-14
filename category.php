<?php
require_once 'config.php';
require_once 'functions.php';

// DB接続
global $db;

// ===== URLパラメータ取得 =====
$category_id = $_GET['id'] ?? null;
$parent_id = $_GET['parent'] ?? null;
$search = $_GET['search'] ?? '';
$search = trim($search);

// search パラメータのみで id がない場合、全カテゴリから検索
if (empty($category_id) && empty($parent_id) && !empty($search)) {
    $parent_id = 'all';
}

// parent_id と category_id の両方が指定されていないかチェック
if ($category_id === null && $parent_id === null) {
    http_response_code(400);
    die('カテゴリが指定されていません。');
}

// ===== ナビバー用カテゴリリスト取得 =====
$navbar_categories = getCategoryTree();

// ===== 検索キーワード用ナビバー情報 =====
$navbar_search = $search;

// ===== 大カテゴリが指定された場合：小カテゴリをグループ表示または初期小カテゴリを選択 =====
$subcategories = [];
$show_grouped_subcategories = false;
$current_subcategory_index = -1; // 現在表示中の小カテゴリのインデックス
$parent_category_name = null; // 大カテゴリ名を保持

if ($parent_id !== null) {
    if ($parent_id === 'all') {
        $page_title = 'すべてのカテゴリ';
        $parent_category_name = 'すべてのカテゴリ';
    } else {
        $parent_cat = $db->selectOne(
            'SELECT id, name FROM categories WHERE id = ?',
            [(int)$parent_id]
        );
        if (!$parent_cat) {
            http_response_code(404);
            die('カテゴリが見つかりません。');
        }
        $page_title = $parent_cat['name'];
        $parent_category_name = $parent_cat['name']; // 大カテゴリ名を保存
    }

    // 大カテゴリ配下の小カテゴリを取得
    if ($parent_id === 'all') {
        $subcategories = $db->select(
            "SELECT sc.id, sc.name, sc.display_order, COUNT(s.id) as sound_count
             FROM sub_categories sc
             LEFT JOIN sounds s ON sc.id = s.category_id AND s.is_public = 1
             GROUP BY sc.id, sc.name, sc.display_order
             ORDER BY sc.display_order ASC, sc.created_at ASC"
        );
    } else {
        $subcategories = getSubCategories((int)$parent_id);
    }

    // 小カテゴリが指定されていない場合、最初の小カテゴリを自動選択
    if ($category_id === null && !empty($subcategories)) {
        $category_id = $subcategories[0]['id'];
        $current_subcategory_index = 0;
    }

    // 小カテゴリが複数ある場合、グループ表示ではなく個別表示
    $show_grouped_subcategories = false;
}

// ===== 小カテゴリが指定された場合：レコード表示 =====
$sounds = [];
if ($category_id !== null) {
    // 小カテゴリ情報取得
    if ($category_id === 'uncategorized') {
        $page_title = 'その他';
        $category = ['id' => null, 'name' => 'その他'];
    } else {
        $cat = $db->selectOne(
            'SELECT sc.id, sc.name FROM sub_categories sc WHERE sc.id = ?',
            [(int)$category_id]
        );
        if (!$cat) {
            http_response_code(404);
            die('カテゴリが見つかりません。');
        }
        $page_title = $cat['name'];
        $category = $cat;
    }

    // 音源取得
    $sound_sql = 'SELECT * FROM sounds WHERE is_public = 1';
    $sound_params = [];

    // カテゴリ絞り込み
    if ($category_id === 'uncategorized') {
        $sound_sql .= ' AND category_id IS NULL';
    } else {
        $sound_sql .= ' AND category_id = ?';
        $sound_params[] = (int)$category_id;
    }

    // 検索条件があれば追加
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

    // 小カテゴリが複数ある場合、現在の小カテゴリのインデックスを取得
    if ($parent_id !== null && !empty($subcategories) && $current_subcategory_index === -1) {
        foreach ($subcategories as $idx => $sub) {
            if ((int)$sub['id'] === (int)$category_id) {
                $current_subcategory_index = $idx;
                break;
            }
        }
    }
}


require_once 'public/header.php';
?>

<!-- メインコンテンツ -->
<div>
    <!-- ページタイトル：大カテゴリ名と小カテゴリ導線 -->
    <?php if ($parent_id !== null && !empty($subcategories)): ?>
        <div class="category-header-section">
            <div class="category-header-container">
                <h1 class="category-header-title"><?php echo esc($parent_category_name); ?></h1>
                <div class="subcategory-nav-links">
                    <?php foreach ($subcategories as $idx => $sub): ?>
                        <?php if ((int)$sub['id'] === (int)$category_id): ?>
                            <span class="subcategory-nav-link subcategory-nav-link-active">
                                <?php echo esc($sub['name']); ?>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/category.php?id=<?php echo esc($sub['id']); ?>&parent=<?php echo esc($parent_id); ?>"
                                class="subcategory-nav-link">
                                <?php echo esc($sub['name']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <style>
            .category-header-section {
                background: rgba(255, 255, 255, 0.08);
                border-radius: 12px;
                padding: 12px 16px;
                margin-bottom: -25px;
                backdrop-filter: blur(10px);
            }

            .category-header-container {
                display: flex;
                align-items: center;
                gap: 16px;
                flex-wrap: wrap;
            }

            .category-header-title {
                font-size: 45px;
                font-weight: 730;
                color: #f3eded;
                margin: 9;
                font-family: 'LINESeedJP', sans-serif;
                white-space: nowrap;
                text-shadow:
                    1px 2px 0 #000000,
                    -1px 2px 0 #000000,
                    1px -1px 0 #000000,
                    -1px -1px 0 #000000;
            }

            .subcategory-nav-links {
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
                align-items: center;
            }

            .subcategory-nav-link {
                display: inline-flex;
                align-items: center;
                padding: 7px 16px;
                background: white;
                border: 1.75px solid #ffc9fa;
                border-radius: 10px;
                text-decoration: none;
                color: #363535;
                font-size: 20px;
                font-weight: 480;
                transition: all 0.2s ease;
                cursor: pointer;
                font-family: 'LINESeedJP', sans-serif;
                white-space: nowrap;
            }

            .subcategory-nav-link:hover {
                background: #f5f7ff;
                border-color: #667eea;
                color: #667eea;
                transform: translateY(-1px);
            }

            .subcategory-nav-link-active {
                background: linear-gradient(135deg, rgb(255, 244, 255) 0%, #fce6ff 100%);
                color: black;
                border-color: #000000;
                cursor: default;
            }

            .subcategory-nav-link-active:hover {
                background: linear-gradient(135deg, rgb(255, 233, 255) 0%, #fce6ff 100%);
                border-color: #000000;
                transform: none;
            }

            @media (max-width: 768px) {
                .category-header-section {
                    padding: 10px 12px;
                    margin-bottom: 20px;
                }

                .category-header-title {
                    font-size: 16px;
                }

                .category-header-container {
                    gap: 10px;
                }

                .subcategory-nav-link {
                    padding: 5px 10px;
                    font-size: 11px;
                }
            }
        </style>
    <?php endif; ?>

    <!-- ページタイトル（小カテゴリの場合） -->
    <h2 class="category-title">
        <?php
        if ($category_id !== 'all') {
            echo '　' . esc($page_title);
        } else {
            echo '　' . esc($page_title);
        }
        ?>
    </h2>

    <?php if (!empty($search)): ?>
        <p style="text-align: center; color: #666; margin: 10px 0 30px 0;">検索: "<?php echo esc($search); ?>"</p>
    <?php endif; ?>

    <!-- 大カテゴリ配下の小カテゴリグループ表示 -->
    <?php if ($show_grouped_subcategories && !empty($subcategories)): ?>
        <section class="subcategory-group-section">
            <div class="subcategory-group-grid">
                <?php foreach ($subcategories as $sub): ?>
                    <a href="<?php echo SITE_URL; ?>/category.php?id=<?php echo esc($sub['id']); ?>&parent=<?php echo esc($parent_id === 'all' ? 0 : $parent_id); ?>"
                        class="subcategory-group-card">
                        <div class="subcategory-group-name"><?php echo esc($sub['name']); ?></div>
                        <div class="subcategory-group-count"><?php echo (int)$sub['sound_count']; ?> 件</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <style>
            .subcategory-group-section {
                margin: 30px 0;
            }

            .subcategory-group-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
            }

            .subcategory-group-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 12px;
                text-decoration: none;
                color: white;
                transition: all 0.3s ease;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                min-height: 140px;
            }

            .subcategory-group-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            }

            .subcategory-group-name {
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 8px;
                text-align: center;
            }

            .subcategory-group-count {
                font-size: 13px;
                opacity: 0.9;
            }

            @media (max-width: 768px) {
                .subcategory-group-grid {
                    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                    gap: 12px;
                }

                .subcategory-group-card {
                    min-height: 120px;
                    padding: 15px;
                }

                .subcategory-group-name {
                    font-size: 14px;
                }
            }
        </style>
    <?php endif; ?>

    <!-- 今週のおすすめレコード セクション -->
    <?php
    // おすすめレコード取得（最大4件）
    $featured_sounds = $db->select(
        "SELECT s.id, s.title, s.description, s.filename, 
                GROUP_CONCAT(t.tag_name SEPARATOR ',') as tags_concat
         FROM sounds s
         LEFT JOIN tags t ON s.id = t.sound_id
         WHERE s.id IN (SELECT sound_id FROM featured_records ORDER BY display_order ASC LIMIT 4)
         GROUP BY s.id
         ORDER BY (SELECT display_order FROM featured_records WHERE sound_id = s.id) ASC"
    );
    ?>

    <?php if (!empty($featured_sounds)): ?>
        <section class="featured-records" aria-label="今週のおすすめレコード">
            <div class="featured-records-header">
                <h2 class="featured-records-title">⭐ 今週のおすすめレコード</h2>
            </div>
            <div class="featured-records-grid">
                <?php foreach ($featured_sounds as $sound): ?>
                    <div class="featured-record-item">
                        <!-- カスタムプレイヤー -->
                        <div class="featured-player">
                            <div class="custom-player">
                                <button class="play-btn">▶</button>
                                <div class="progress-container">
                                    <div class="progress-bar-wrapper">
                                        <div class="progress-bar"></div>
                                    </div>
                                    <div class="time-display">0:00 / 0:00</div>
                                </div>
                                <audio class="audio-player">
                                    <source src="<?php echo SITE_URL . PUBLIC_UPLOAD_DIR . '/' . esc($sound['filename']); ?>" type="audio/mpeg">
                                </audio>
                            </div>
                        </div>

                        <!-- 情報表示 -->
                        <div class="featured-info">
                            <h3 class="featured-title"><?php echo esc($sound['title']); ?></h3>

                            <?php if (!empty($sound['tags_concat'])): ?>
                                <div class="featured-tags">
                                    <?php foreach (explode(',', $sound['tags_concat']) as $tag): ?>
                                        <span class="tag-badge">#<?php echo esc($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($sound['description'])): ?>
                                <p class="featured-description"><?php echo esc($sound['description']); ?></p>
                            <?php endif; ?>

                            <a href="download.php?id=<?php echo $sound['id']; ?>" class="featured-download-btn">
                                ダウンロード
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <style>
            .featured-records {
                margin: 40px 0;
            }

            .featured-records-header {
                padding: 20px 0;
                border-bottom: 3px solid #667eea;
                margin-bottom: 20px;
            }

            .featured-records-title {
                font-size: 1.8em;
                margin: 0;
                color: #333;
                font-weight: bold;
            }

            .featured-records-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .featured-record-item {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 15px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .featured-record-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            }

            .featured-player {
                margin-bottom: 15px;
            }

            .featured-info {
                padding-top: 10px;
            }

            .featured-title {
                font-size: 1.1em;
                margin: 0 0 8px 0;
                color: #333;
                font-weight: bold;
                line-height: 1.4;
            }

            .featured-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                margin-bottom: 8px;
            }

            .featured-description {
                font-size: 0.9em;
                color: #666;
                margin: 8px 0;
                line-height: 1.4;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .featured-download-btn {
                display: inline-block;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 8px 16px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: 600;
                font-size: 0.9em;
                transition: opacity 0.3s ease;
                margin-top: 8px;
            }

            .featured-download-btn:hover {
                opacity: 0.9;
                color: white;
            }

            @media (max-width: 768px) {
                .featured-records-title {
                    font-size: 1.5em;
                }

                .featured-records-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    <?php endif; ?>

    <!-- 音源一覧表示 -->
    <?php if (empty($sounds)): ?>
        <div class="no-data">
            <p>申し訳ありません、該当する音源がありません。</p>
        </div>
    <?php else: ?>
        <div class="sounds-grid">
            <?php foreach ($sounds as $sound): ?>
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
                                    <source src="<?php echo SITE_URL . PUBLIC_UPLOAD_DIR . '/' . esc($sound['filename']); ?>" type="audio/mpeg">
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
    <?php endif; ?>

    <!-- ページネーション（小カテゴリ切り替え）-->
    <?php if ($parent_id !== null && !empty($subcategories) && count($subcategories) > 1): ?>
        <nav class="pagination-section">
            <div class="pagination-container">
                <!-- 前へ -->
                <?php if ($current_subcategory_index > 0): ?>
                    <?php $prev_sub = $subcategories[$current_subcategory_index - 1]; ?>
                    <a href="<?php echo SITE_URL; ?>/category.php?id=<?php echo esc($prev_sub['id']); ?>&parent=<?php echo esc($parent_id); ?>"
                        class="pagination-btn pagination-prev">
                        ← 前へ（<?php echo esc($prev_sub['name']); ?>）
                    </a>
                <?php else: ?>
                    <div class="pagination-btn pagination-btn-disabled">← 前へ</div>
                <?php endif; ?>

                <!-- ページ番号 -->
                <div class="pagination-numbers">
                    <?php for ($i = 0; $i < count($subcategories); $i++): ?>
                        <?php $sub = $subcategories[$i]; ?>
                        <?php if ($i === $current_subcategory_index): ?>
                            <span class="pagination-number pagination-number-active" title="<?php echo esc($sub['name']); ?>">
                                <?php echo $i + 1; ?>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/category.php?id=<?php echo esc($sub['id']); ?>&parent=<?php echo esc($parent_id); ?>"
                                class="pagination-number"
                                title="<?php echo esc($sub['name']); ?>">
                                <?php echo $i + 1; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <!-- 次へ -->
                <?php if ($current_subcategory_index < count($subcategories) - 1): ?>
                    <?php $next_sub = $subcategories[$current_subcategory_index + 1]; ?>
                    <a href="<?php echo SITE_URL; ?>/category.php?id=<?php echo esc($next_sub['id']); ?>&parent=<?php echo esc($parent_id); ?>"
                        class="pagination-btn pagination-next">
                        次へ（<?php echo esc($next_sub['name']); ?>） →
                    </a>
                <?php else: ?>
                    <div class="pagination-btn pagination-btn-disabled">次へ →</div>
                <?php endif; ?>
            </div>
        </nav>

        <style>
            .pagination-section {
                margin-top: 40px;
                padding: 20px 0;
            }

            .pagination-container {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 15px;
                flex-wrap: wrap;
                max-width: 100%;
            }

            .pagination-btn {
                display: inline-block;
                padding: 6px 6px;
                background: linear-gradient(150deg, #d1baff 0%, #7ac1ff 100%);
                color: white;
                font-family: 'LINESeedJP', sans-serif;
                text-decoration: none;
                border-radius: 10px;
                transition: all 0.3s ease;
                font-weight: 900;
                font-size: 28px;
                white-space: nowrap;
                -webkit-text-stroke: 0.5px #000000;
        text-shadow:
                    0px 0px 1px #000000,
                    1px 1px 1px #000000,
                    1.2px 1px 1px #000000,
                    1.2px 1px 1px #000000;
            }

            .pagination-btn:hover {
                background: linear-gradient(150deg, #c7aaff 0%, #5981f1 100%);
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }

            .pagination-btn-disabled {
                display: inline-block;
                padding: 5px 16px;
                background-color: #cccccc;
                color: #c5c5c5;
                font-family: 'LINESeedJP', sans-serif;
                border-radius: 10px;
                font-weight: 800;
                white-space: nowrap;
                cursor: not-allowed;
            }

            .pagination-numbers {
                display: flex;
                gap: 8px;
                align-items: center;
                flex-wrap: wrap;
            }

            .pagination-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background-color: #f0f0f0;
                color: #5a5a5a;
                text-decoration: none;
                border-radius: 4px;
                border: 2px solid transparent;
                transition: all 0.3s ease;
                font-weight: 500;
                cursor: pointer;
            }

            .pagination-number:hover {
                background-color: #e8e8e8;
                border-color: #667eea;
            }

            .pagination-number-active {
                background-color: #667eea;
                color: white;
                border-color: #667eea;
            }

            @media (max-width: 768px) {
                .pagination-container {
                    gap: 10px;
                }

                .pagination-btn {
                    padding: 8px 12px;
                    font-size: 0.9em;
                }

                .pagination-number {
                    width: 36px;
                    height: 36px;
                    font-size: 0.9em;
                }
            }
        </style>
    <?php endif; ?>
</div>

<script>
    // ===== カスタムプレイヤー制御 =====
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.custom-player').forEach(playerContainer => {
            const audio = playerContainer.querySelector('.audio-player');
            const playBtn = playerContainer.querySelector('.play-btn');
            const progressBar = playerContainer.querySelector('.progress-bar');
            const progressBarWrapper = playerContainer.querySelector('.progress-bar-wrapper');
            const timeDisplay = playerContainer.querySelector('.time-display');
            const volumeSlider = playerContainer.querySelector('.volume-slider');

            // 再生/一時停止
            playBtn.addEventListener('click', () => {
                if (audio.paused) {
                    audio.play();
                    playBtn.textContent = '⏸';
                } else {
                    audio.pause();
                    playBtn.textContent = '▶';
                }
            });

            // 再生時間更新
            audio.addEventListener('timeupdate', () => {
                const percent = (audio.currentTime / audio.duration) * 100;
                progressBar.style.width = percent + '%';
                updateTimeDisplay();
            });

            // 音声終了時
            audio.addEventListener('ended', () => {
                playBtn.textContent = '▶';
            });

            // プログレスバークリック
            progressBarWrapper.addEventListener('click', (e) => {
                const rect = progressBarWrapper.getBoundingClientRect();
                const percent = (e.clientX - rect.left) / rect.width;
                audio.currentTime = percent * audio.duration;
            });

            // ボリューム制御
            if (volumeSlider) {
                volumeSlider.addEventListener('input', () => {
                    audio.volume = volumeSlider.value / 100;
                });
            }

            // 時間表示更新
            function updateTimeDisplay() {
                const current = formatTime(audio.currentTime);
                const duration = formatTime(audio.duration);
                timeDisplay.textContent = current + ' / ' + duration;
            }

            function formatTime(seconds) {
                if (isNaN(seconds)) return '0:00';
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return mins + ':' + (secs < 10 ? '0' : '') + secs;
            }

            // 初期化
            audio.volume = volumeSlider.value / 100;
            audio.addEventListener('loadedmetadata', updateTimeDisplay);
        });
    });
</script>