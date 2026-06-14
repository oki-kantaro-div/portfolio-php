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

// ===== カテゴリー一覧取得（ナビバーにも使用） =====
$navbar_categories = getCategoryTree();

// ===== TOP: お知らせ（公開中の上位3件） =====
$now = date('Y-m-d H:i:s');
$news_items = $db->select(
    "SELECT id, title, body, is_important, publish_start, publish_end
     FROM NewsRelease
     WHERE publish_start <= ?
       AND (publish_end IS NULL OR publish_end = '' OR publish_end >= ?)
     ORDER BY is_important DESC, publish_start DESC, id DESC
     LIMIT 3",
    [$now, $now]
);

require_once 'public/header.php';
?>

<style>
    .telop-section,
    .telop-container {
        background: transparent;
    }

    .telop-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 40px;
        padding: 40px 20px;
        margin-bottom: -60px;
    }

    .telop-image-left,
    .telop-image-right {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .telop-image-left img,
    .telop-image-right img {
        max-width: 350px;
        height: auto;
        aspect-ratio: 993 / 1345;
        object-fit: contain;
        opacity: 0;
        transform: translateY(100vh);
        transition: opacity 0.8s ease-in-out, transform 0.8s ease-in-out;
    }

    .telop-image-left.fade-in img,
    .telop-image-right.fade-in img {
        opacity: 1;
        transform: translateY(0);
    }

    .telop-text {
       font-size: 46px;
        font-weight: 880;
        color: #ffffa4;
        margin: 8;
        font-family: 'LINESeedJP', sans-serif;
        white-space: nowrap;
        -webkit-text-stroke: 1.5px #000000;
        text-shadow:
                    1px 2px 1px #000000,
                    -1px 2px 1px #000000,
                    1px -1px 1px #000000,
                    2px 1px 1px #000000;
    }

    .telop-text-main {
        font-size: 132px;
        font-weight: 1500;
        color: #ffebfb;
        margin: 10;
        font-family: 'LINESeedJP', sans-serif;
        white-space: nowrap;
        -webkit-text-stroke:4.5px #000000;
        text-shadow:
                    1px 2px 4.5px #000000,
                    -1px 2px 4.5px #000000,
                    3px 4px 4.5px #000000,
                    3px 4px 4.5px #000000;
    }

    @media (max-width: 768px) {
        .telop-container {
            flex-direction: column;
            gap: 20px;
        }

        .telop-image-left,
        .telop-image-right {
            display: none;
        }

        .telop-text-main {
            padding-left: 10px;
            font-size: 1.5em;
        }
    }
</style>

<!-- メインコンテンツ -->
<div>

    <!-- テロップセクション -->
    <section class="" aria-label="ウェルカムメッセージ">
        <div class="telop-container">
            <div class="telop-image-left">
                <img src="assets/images/1780805783840.png" alt="デコレーション左">
            </div>
            <h1 class="telop-text">
                <span class="telop-text-main">オトリウムへようこそ</span>
            </h1>
            <div class="telop-image-right">
                <img src="assets/images/sample.png" alt="デコレーション右">
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const telopText = document.querySelector('.telop-text');
            if (!telopText) return;

            const originalHTML = telopText.innerHTML;
            telopText.innerHTML = '';
            const charDelay = 50; // ミリ秒

            // HTMLタグと テキストを分離
            const parts = originalHTML.split(/(<[^>]*>)/);

            let displayedContent = '';
            let charCount = 0;
            let targetChars = 0;
            // 全テキスト文字をカウント（タグは除外）
            for (let part of parts) {
                if (!part.match(/^<[^>]*>$/)) {
                    targetChars += part.length;
                }
            }

            const type = () => {
                if (charCount >= targetChars) {
                    // 完全に表示
                    telopText.innerHTML = originalHTML;
                    // 画像をフェードイン
                    const imageLeft = document.querySelector('.telop-image-left');
                    const imageRight = document.querySelector('.telop-image-right');
                    if (imageLeft) imageLeft.classList.add('fade-in');
                    if (imageRight) imageRight.classList.add('fade-in');
                    return;
                }

                displayedContent = '';
                let currentCharCount = 0;

                // parts をループして、charCount 文字までを表示
                for (let part of parts) {
                    if (part.match(/^<[^>]*>$/)) {
                        // タグはそのまま追加
                        displayedContent += part;
                    } else {
                        // テキストの場合
                        for (let char of part) {
                            if (currentCharCount < charCount + 1) {
                                displayedContent += char;
                                currentCharCount++;
                            } else {
                                break;
                            }
                        }
                    }
                }

                telopText.innerHTML = displayedContent;
                charCount++;
                setTimeout(type, charDelay);
            };

            type();
        });
    </script>



    <!-- 今週のおすすめレコード セクション -->
    <?php
    // おすすめレコード取得（最大4件）
    $featured_sounds = $db->select(
        "SELECT s.id, s.title, s.description, s.filename, 
                GROUP_CONCAT(t.tag_name SEPARATOR ',') as tags_concat,
                fr.display_order
         FROM featured_records fr
         JOIN sounds s ON fr.sound_id = s.id
         LEFT JOIN tags t ON s.id = t.sound_id
         GROUP BY s.id, fr.display_order
         ORDER BY fr.display_order ASC
         LIMIT 4"
    );
    ?>

    <?php if (!empty($featured_sounds)): ?>
        <section class="featured-records" aria-label="今週のおすすめレコード">
            <div class="featured-records-header">
                <h2 class="telop-text">　【今週のおすすめ効果音】</h2>
            </div>
            <div class="sounds-grid">
                <?php foreach ($featured_sounds as $sound): ?>
                    <div class="sound-row">
                        <!-- 左30% : タイトル -->
                        <div class="sound-row-title">
                            <span class="title-text"><?php echo esc($sound['title']); ?></span>
                        </div>

                        <!-- 中40% : タグと説明 -->
                        <div class="sound-row-middle">
                            <!-- タグ表示 -->
                            <?php if (!empty($sound['tags_concat'])): ?>
                                <div class="tags-section">
                                    <?php foreach (explode(',', $sound['tags_concat']) as $tag): ?>
                                        <span class="tag-badge">#<?php echo esc($tag); ?></span>
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
        </section>

        <style>
            .featured-records {
                margin: 40px 0;
            }

            .featured-records-header {
                padding: 20px 0;
                margin-bottom: 20px;
            }

            .featured-records-title {
                font-size: 2.3em;
                margin: 0;
                color: #242428;
                font-weight: bold;
            }
        </style>

        <script>
            // ===== おすすめレコード カスタムプレイヤー制御 =====
            document.addEventListener('DOMContentLoaded', () => {
                const section = document.querySelector('.featured-records');
                if (!section) return;

                section.querySelectorAll('.custom-player').forEach(playerContainer => {
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
                    audio.volume = volumeSlider ? volumeSlider.value / 100 : 0.8;
                    audio.addEventListener('loadedmetadata', updateTimeDisplay);
                });
            });
        </script>
    <?php endif; ?>
    <!-- <?php if (!empty($news_items)): ?>
        <section class="top-news" aria-label="お知らせ">
            <div class="top-news-header">
                <h2 class="top-news-title">お知らせ</h2>
            </div>
            <div class="top-news-carousel" data-autoplay-ms="5000">
                <div class="top-news-track">
                    <?php foreach ($news_items as $n): ?>
                        <article class="top-news-slide">
                            <div class="top-news-item <?php echo ((int)$n['is_important'] === 1) ? 'is-important' : ''; ?>">
                                <div class="top-news-item-head">
                                    <div class="top-news-item-title">
                                        <?php if ((int)$n['is_important'] === 1): ?>
                                            <span class="top-news-badge">重要</span>
                                        <?php endif; ?>
                                        <span><?php echo esc($n['title']); ?></span>
                                    </div>
                                    <div class="top-news-item-date">
                                        <small>
                                            <?php echo esc(date('Y/m/d', strtotime($n['publish_start']))); ?>
                                            <?php if (!empty($n['publish_end'])): ?>
                                                〜 <?php echo esc(date('Y/m/d', strtotime($n['publish_end']))); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="top-news-item-body">
                                    <?php echo nl2br(esc($n['body'])); ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section> -->

        <script>
            (function() {
                const carousel = document.querySelector('.top-news-carousel');
                if (!carousel) return;

                const track = carousel.querySelector('.top-news-track');
                const slides = Array.from(carousel.querySelectorAll('.top-news-slide'));
                if (!track || slides.length <= 1) return;

                const autoplayMs = Number(carousel.getAttribute('data-autoplay-ms')) || 5000;
                const transitionMs = 900;
                const thresholdPx = 40;
                const slideCount = slides.length;

                // ---- infinite loop via clones ----
                const firstClone = slides[0].cloneNode(true);
                const lastClone = slides[slides.length - 1].cloneNode(true);
                firstClone.setAttribute('data-clone', '1');
                lastClone.setAttribute('data-clone', '1');
                track.insertBefore(lastClone, slides[0]);
                track.appendChild(firstClone);

                let index = 1; // 0=lastClone, 1=real first ... slideCount=real last, slideCount+1=firstClone
                let timer = null;
                let paused = false;
                let dragging = false;
                let startX = 0;
                let deltaX = 0;
                let width = 1;

                const setTransition = (enabled) => {
                    track.style.transition = enabled ? `transform ${transitionMs}ms ease` : 'none';
                };

                const measure = () => {
                    width = carousel.getBoundingClientRect().width || 1;
                };

                const setTranslatePx = (px) => {
                    track.style.transform = `translateX(${px}px)`;
                };

                const goTo = (i, {
                    animate = true
                } = {}) => {
                    index = i;
                    setTransition(animate);
                    setTranslatePx(-index * width);
                };

                const normalizeIfNeeded = () => {
                    if (index === 0) goTo(slideCount, {
                        animate: false
                    });
                    if (index === slideCount + 1) goTo(1, {
                        animate: false
                    });
                };

                const start = () => {
                    if (timer) return;
                    timer = setInterval(() => {
                        if (paused) return;
                        goTo(index + 1, {
                            animate: true
                        });
                    }, autoplayMs);
                };

                const stop = () => {
                    if (!timer) return;
                    clearInterval(timer);
                    timer = null;
                };

                // pause on hover/focus (desktop + keyboard)
                carousel.addEventListener('mouseenter', () => {
                    paused = true;
                });
                carousel.addEventListener('mouseleave', () => {
                    if (!dragging) paused = false;
                });
                carousel.addEventListener('focusin', () => {
                    paused = true;
                });
                carousel.addEventListener('focusout', () => {
                    if (!dragging) paused = false;
                });

                // drag to slide (mouse/touch/pen)
                const onPointerDown = (e) => {
                    if (e.pointerType === 'mouse' && e.button !== 0) return;
                    dragging = true;
                    paused = true;
                    stop();
                    measure();
                    startX = e.clientX;
                    deltaX = 0;
                    setTransition(false);
                    carousel.setPointerCapture?.(e.pointerId);
                    carousel.classList.add('is-dragging');
                };

                const onPointerMove = (e) => {
                    if (!dragging) return;
                    deltaX = e.clientX - startX;
                    setTranslatePx((-index * width) + deltaX);
                };

                const onPointerUp = () => {
                    if (!dragging) return;
                    dragging = false;
                    carousel.classList.remove('is-dragging');
                    const moved = deltaX;

                    if (moved <= -thresholdPx) {
                        goTo(index + 1, {
                            animate: true
                        }); // left -> next
                    } else if (moved >= thresholdPx) {
                        goTo(index - 1, {
                            animate: true
                        }); // right -> prev
                    } else {
                        goTo(index, {
                            animate: true
                        }); // snap back
                    }

                    if (!carousel.matches(':hover')) {
                        paused = false;
                        start();
                    }
                };

                carousel.addEventListener('pointerdown', onPointerDown);
                carousel.addEventListener('pointermove', onPointerMove);
                carousel.addEventListener('pointerup', onPointerUp);
                carousel.addEventListener('pointercancel', onPointerUp);

                // if tab hidden, stop interval to avoid drift
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) stop();
                    else start();
                });

                window.addEventListener('resize', () => {
                    measure();
                    goTo(index, {
                        animate: false
                    });
                });

                track.addEventListener('transitionend', normalizeIfNeeded);

                // initial
                measure();
                goTo(1, {
                    animate: false
                });
                start();
            })();
        </script>
    <?php endif; ?>
</div>

<script>
    // TOPページ：ロード後5秒間操作がなければ自動スクロール（1回だけ判定）
    document.addEventListener('DOMContentLoaded', () => {
        const IDLE_MS = 5000;
        const SCROLL_STEP_PX = 1; // 1tickあたりのスクロール量
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
        activityEvents.forEach(evt => window.addEventListener(evt, onUserActivity, {
            passive: true
        }));

        // タブ非表示中は止める（戻っても再アームしない）
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopAutoScroll();
            }
        });
    });
</script>