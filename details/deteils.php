<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$page_title = '利用規約';

// 利用規約ページは検索/カテゴリ不要
$hide_nav_search = true;
$navbar_search = '';
$navbar_categories = [];

require_once __DIR__ . '/../public/header.php';
?>

<div class="page-with-reveal">
    <h1 class="section-title reveal-item">利用規約</h1>

    <style>
        .reveal-item {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.65s ease, transform 0.65s ease;
            will-change: opacity, transform;
        }

        .reveal-item.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal-item {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        .terms-wrap {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .terms-section {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            margin: 0 28px;
        }

        .terms-title {
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 10px 0;
            color: #2c3e50;
            letter-spacing: -0.3px;
        }

        .terms-body {
            margin: 0;
            line-height: 1.9;
            color: #333;
            font-size: 20px;
            white-space: pre-wrap;
        }
    </style>

    <div class="terms-wrap">
        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ ご利用について</h2>
            <p class="terms-body">オトリウムの効果音は、個人・法人問わず無料でご利用いただけます。
商用利用も可能ですので、動画制作や配信、アプリ、ゲームなど、さまざまな用途でお使いください。
「ちょっと使ってみようかな？」くらいの軽い気持ちで、ぜひお気軽にご利用ください！</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ クレジットの表記について</h2>
            <p class="terms-body">効果音をご利用の際、概要欄やクレジットに「オトリウム」またはサイトURLを記載していただけるととても嬉しいです^_^
こちらは任意のため、難しい場合は無理に記載しなくても構いません。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ 禁止事項</h2>
            <p class="terms-body">以下のような目的でのご利用はご遠慮ください。
・公序良俗に反するコンテンツでの使用
・違法行為や犯罪に関連する用途での使用
・第三者を誹謗中傷する目的での使用
・当サイトや制作者の信用を損なうような使用
また、素材をメインコンテンツとして成立させるような利用（例:素材集としての再配布など）もご遠慮ください。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ 著作権について</h2>
            <p class="terms-body">当サイトで配布している効果音の著作権は、すべて制作者に帰属します。
そのため、以下の行為は禁止とさせていただきます。
①自作発言
②素材そのものの再配布、販売
③素材をそのまま、またはほぼそのままの形で配布する行為
最低限のマナーとして守っていただければと思います。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ 免責事項</h2>
            <p class="terms-body">当サイトの効果音を利用したことによって発生したトラブルや損害について、制作者は一切の責任を負いかねます。
あらかじめご了承ください。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ おわりに</h2>
            <p class="terms-body">最後までお読みいただきまして、誠にありがとうございました！
堅苦しいルールで縛るつもりはありません。
「この音いいじゃーん！使いますー！」くらいの感覚で、どんどん活用していただけたら嬉しいです！
オトリウムが、あなたの作品づくりの力になれたら幸いです^_^</p>
        </section>
    </div>

    <div class="reveal-item" style="display:flex; justify-content:center; margin-top:18px;">
        <a href="<?php echo SITE_URL; ?>/" class="reset-btn" style="min-width: 220px; text-align:center;">
            TOPへ戻る
        </a>
    </div>

    <script>
    (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.querySelectorAll('.page-with-reveal .reveal-item').forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }
        var items = document.querySelectorAll('.page-with-reveal .reveal-item');
        if (!items.length || !('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
        items.forEach(function (el) { obs.observe(el); });
    })();
    </script>
</div>