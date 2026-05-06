<?php
require_once 'config.php';
require_once 'functions.php';

// DB接続
global $db;

// ===== URLパラメータ取得 =====
$category_id = $_GET['id'] ?? null;
$search = $_GET['search'] ?? '';
$search = trim($search);

// search パラメータのみで id がない場合、全カテゴリから検索
if (empty($category_id) && !empty($search)) {
    $category_id = 'all';
}

if ($category_id === null) {
    http_response_code(400);
    die('カテゴリが指定されていません。');
}

// ===== カテゴリ情報取得 =====
$category = null;
$page_title = '';

if ($category_id === 'all') {
    $page_title = 'すべてのカテゴリ';
    $category = ['id' => null, 'name' => 'すべてのカテゴリ'];
} elseif ($category_id === 'uncategorized') {
    $page_title = 'その他';
    $category = ['id' => null, 'name' => 'その他'];
} else {
    $cat = $db->selectOne(
        'SELECT id, name FROM categories WHERE id = ?',
        [(int)$category_id]
    );
    if (!$cat) {
        http_response_code(404);
        die('カテゴリが見つかりません。');
    }
    $page_title = $cat['name'];
    $category = $cat;
}

// ===== ナビバー用カテゴリリスト取得 =====
$sql_categories = "
    SELECT c.id, c.name, c.display_order 
    FROM categories c
    WHERE EXISTS (SELECT 1 FROM sounds WHERE category_id = c.id AND is_public = 1)
    ORDER BY c.display_order ASC, c.created_at ASC
";
$navbar_categories = $db->select($sql_categories);

// ===== 検索キーワード用ナビバー情報 =====
$navbar_search = $search;

// ===== 音源取得 =====
$sound_sql = 'SELECT * FROM sounds WHERE is_public = 1';

$sound_params = [];

// カテゴリ絞り込み（'all' の場合はすべて表示）
if ($category_id === 'uncategorized') {
    $sound_sql .= ' AND category_id IS NULL';
} elseif ($category_id !== 'all') {
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

require_once 'public/header.php';
?>

<!-- メインコンテンツ -->
<div>
    <!-- ページタイトル（修正前のデザイン） -->
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
        volumeSlider.addEventListener('input', () => {
            audio.volume = volumeSlider.value / 100;
        });

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
