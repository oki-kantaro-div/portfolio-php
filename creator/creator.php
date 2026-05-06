<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$page_title = '製作者について';

// 利用規約ページは検索/カテゴリ不要
$hide_nav_search = true;
$navbar_search = '';
$navbar_categories = [];

require_once __DIR__ . '/../public/header.php';
?>

<div class="page-with-reveal">
    <h1 class="section-title reveal-item">製作者について</h1>

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
            <h2 class="terms-title">■ はじめに</h2>
            <p class="terms-body">はじめまして。「オトリウム」を運営している、Y（仮名）です^_^
このページでは、少しだけ自分のことと、このサービスを始めた想いについてお話しします！</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ 音楽との出会い</h2>
            <p class="terms-body">幼少期、親のすすめでドラムを始めたのが音楽との最初の出会いでした。
そこから中学・高校と、ドラムだけでなくギターやベースにも触れ、多くの時間を音楽に費やしてました。
テレビやアニメ、ゲームも全くしなかったので友達の話に合わせるのは苦慮した記憶があります笑</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■恋愛と大学時代</h2>
            <p class="terms-body">大学時代はというと……正直に言うと、恋愛に夢中になってしまい、音楽から少し離れていた時期がありました。
ですが、その後の失恋をきっかけに、もう一度音楽と向き合うようになりました。
以前よりも深く音楽に取り組むことができ、自分の音楽スキルとしても大きく成長したと感じています。
そしてなにより、共に夢を目指す仲間とも出会うことができたので母校に入って本当によかったなーと思います！</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ オトリウムを始めた理由</h2>
            <p class="terms-body">昨今では生成AIの進化もあり、クリエイティブの形が大きく変わってきています。
その中で、「自分で何かを作る機会」が減ってきていると感じることもありました。
だからこそ、
少しでもクリエイターの力になれるものを作りたい
そんな想いから、「オトリウム」を立ち上げました。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ 最後に</h2>
            <p class="terms-body">このサイトの効果音が、誰かの動画や作品づくりの一部になって、ほんの少しでもその人の活動や人生を明るくできたら、とても嬉しいです。

気軽に！自由に！！どんどん使ってもらえたら嬉しいです！
最後までお読みいただいて、ありがとうございました！^_^</p>
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