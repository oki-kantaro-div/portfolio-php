<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$page_title = 'プライバシーポリシー';

// 利用規約ページは検索/カテゴリ不要
$hide_nav_search = true;
$navbar_search = '';
$navbar_categories = [];

require_once __DIR__ . '/../public/header.php';
?>

<div class="page-with-reveal">
    <h1 class="section-title reveal-item">プライバシーポリシー</h1>

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
            <h2 class="terms-title">■個人情報の利用について</h2>
            <p class="terms-body">オトリウムの効果音は、個人・法人問わず無料でご利用いただけます。
商用利用も可能ですので、動画制作や配信、アプリ、ゲームなど、さまざまな用途でお使いください。
「ちょっと使ってみようかな？」くらいの軽い気持ちで、ぜひお気軽にご利用ください！当サイト（以下、オトリウム）では、お問い合わせや効果音リクエストなどの際に、名前やメールアドレス等の個人情報をご入力いただく場合がございます。

これらの個人情報は、ご質問への回答や必要な情報をご連絡する目的以外では使用いたしません。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■広告について</h2>
            <p class="terms-body">当サイトでは、第三者配信の広告サービス「Google AdSense」を利用いたします。

Googleなどの第三者広告配信事業者は、ユーザーの興味に応じた広告を表示するために、Cookieを使用することがあります。

Cookieを使用することで、当サイトはユーザーのコンピュータを識別できるようになりますが、個人を特定するものではありません。

Cookieの使用を望まない場合は、ブラウザの設定から無効にすることが可能です。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■アクセス解析ツールについて</h2>
            <p class="terms-body">当サイトでは、アクセス解析ツール（例:Googleアナリティクス）を利用する場合があります。

このツールはトラフィックデータの収集のためにCookieを使用しています。
収集されるデータは匿名であり、個人を特定するものではありません。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ 免責事項</h2>
            <p class="terms-body">当サイトからリンクやバナーなどによって他のサイトへ移動された場合、移動先サイトで提供される情報やサービス等について一切の責任を負いません。

また、当サイトのコンテンツ・情報については可能な限り正確な情報を掲載するよう努めておりますが、誤情報が入り込んだり、情報が古くなっている場合があります。

掲載内容によって生じた損害等については、一切の責任を負いかねますのでご了承ください。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ 著作権について</h2>
            <p class="terms-body">当サイトに掲載されているコンテンツの著作権は、制作者に帰属します。
            無断転載や不正利用を禁止します。</p>
        </section>

        <section class="terms-section reveal-item">
            <h2 class="terms-title">■ プライバシーポリシーの変更について</h2>
            <p class="terms-body">当サイトは、必要に応じて本ポリシーの内容を変更することがあります。

変更後のプライバシーポリシーは、本ページにて公表された時点で有効となります。</p>
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