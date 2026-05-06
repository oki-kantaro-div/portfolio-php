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
$sql_categories = "
    SELECT c.id, c.name, c.display_order 
    FROM categories c
    WHERE EXISTS (SELECT 1 FROM sounds WHERE category_id = c.id AND is_public = 1)
    ORDER BY c.display_order ASC, c.created_at ASC
";

$categories = $db->select($sql_categories);
$navbar_categories = $categories;

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


<!-- メインコンテンツ -->
<div>

    <!-- テロップセクション -->
    <section class="telop-section" aria-label="ウェルカムメッセージ">
        <div class="telop-container">
            <h1 class="telop-text">
                オトリウムへようこそ！！<br>
                早速効果音を探してみましょう
            </h1>
        </div>
    </section>

    <!-- カテゴリリスト -->
    <?php foreach ($categories as $cat): ?>
        <h2 class="category-title">
            <a href="<?php echo SITE_URL; ?>/category.php?id=<?php echo $cat['id']; ?>" style="text-decoration: none; color: inherit;">
                　<?php echo esc($cat['name']); ?>
            </a>
        </h2>
    <?php endforeach; ?>
    <h2 class="category-title">
        <a href="<?php echo SITE_URL; ?>/category.php?id=uncategorized" style="text-decoration: none; color: inherit;">
            　その他
        </a>
    </h2>

    <?php if (!empty($news_items)): ?>
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
        </section>

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

            const goTo = (i, { animate = true } = {}) => {
                index = i;
                setTransition(animate);
                setTranslatePx(-index * width);
            };

            const normalizeIfNeeded = () => {
                if (index === 0) goTo(slideCount, { animate: false });
                if (index === slideCount + 1) goTo(1, { animate: false });
            };

            const start = () => {
                if (timer) return;
                timer = setInterval(() => {
                    if (paused) return;
                    goTo(index + 1, { animate: true });
                }, autoplayMs);
            };

            const stop = () => {
                if (!timer) return;
                clearInterval(timer);
                timer = null;
            };

            // pause on hover/focus (desktop + keyboard)
            carousel.addEventListener('mouseenter', () => { paused = true; });
            carousel.addEventListener('mouseleave', () => { if (!dragging) paused = false; });
            carousel.addEventListener('focusin', () => { paused = true; });
            carousel.addEventListener('focusout', () => { if (!dragging) paused = false; });

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
                    goTo(index + 1, { animate: true }); // left -> next
                } else if (moved >= thresholdPx) {
                    goTo(index - 1, { animate: true }); // right -> prev
                } else {
                    goTo(index, { animate: true }); // snap back
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
                goTo(index, { animate: false });
            });

            track.addEventListener('transitionend', normalizeIfNeeded);

            // initial
            measure();
            goTo(1, { animate: false });
            start();
        })();
        </script>
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