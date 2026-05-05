<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? esc($page_title) . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        /* ===== LINESeedJP フォント定義 ===== */
        @font-face {
            font-family: 'LINESeedJP';
            src: url('./assets/font/LINESeedJP_20241105/Web/WOFF2/LINESeedJP_OTF_Th.woff2') format('woff2');
            font-weight: 100;
            font-display: swap;
        }

        @font-face {
            font-family: 'LINESeedJP';
            src: url('./assets/font/LINESeedJP_20241105/Web/WOFF2/LINESeedJP_OTF_Rg.woff2') format('woff2');
            font-weight: 400;
            font-display: swap;
        }

        @font-face {
            font-family: 'LINESeedJP';
            src: url('./assets/font/LINESeedJP_20241105/Web/WOFF2/LINESeedJP_OTF_Bd.woff2') format('woff2');
            font-weight: 700;
            font-display: swap;
        }

        @font-face {
            font-family: 'LINESeedJP';
            src: url('./assets/font/LINESeedJP_20241105/Web/WOFF2/LINESeedJP_OTF_Eb.woff2') format('woff2');
            font-weight: 800;
            font-display: swap;
        }
        
        
        body {
            background: linear-gradient(135deg,rgb(182, 242, 255) 0%,rgb(241, 203, 255) 50%,rgb(158, 237, 255) 100%);
            background-size: 200% 200%;
            animation: gradientFlow 15s ease infinite;
            font-family: 'LINESeedJP', 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        
        @keyframes gradientFlow {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .navbar {
            background: linear-gradient(135deg,rgb(252, 246, 255) 0%,rgb(242, 222, 255) 100%) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 0.5rem 1rem !important;
            position: sticky;
            top: 0;
            z-index: 1000;
            min-height: auto;
            display: flex;
            align-items: center;
            overflow: visible;
            justify-content: space-between;
        }

        .navbar-brand {
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            font-family: 'LINESeedJP', sans-serif;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #333 !important;
            margin: 0;
            white-space: nowrap;
        }

        .navbar-logo {
            height: 45px;
            width: auto;
            object-fit: contain;
            display: inline-block;
            flex-shrink: 0;
            vertical-align: middle;
        }

        .navbar-brand:hover {
            color: #667eea !important;
        }

        .brand-name-img {
            height: 35px;
            width: auto;
            object-fit: contain;
            display: inline-block;
            vertical-align: middle;
        }

        .navbar-start {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 0 0 auto;
            order: 0;
        }

        .navbar-search {
            display: none;
            gap: 6px;
            align-items: center;
            flex: 0 0 auto;
            order: 1;
        }

        .navbar-categories {
            display: none;
            align-items: center;
            gap: 10px;
            flex: 1 1 auto;
            min-width: 0;
            order: 1;
        }

        .navbar-categories-label {
            font-size: 12px;
            font-weight: 700;
            color: #555;
            white-space: nowrap;
        }

        .navbar-categories-list {
            display: flex;
            gap: 8px;
            align-items: center;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            -webkit-overflow-scrolling: touch;
            padding: 2px 0;
            scrollbar-width: thin;
            min-width: 0;
        }

        .navbar-categories-list::-webkit-scrollbar {
            height: 6px;
        }

        .navbar-categories-list::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.18);
            border-radius: 999px;
        }

        .navbar-category-link {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.65);
            border: 1px solid rgba(0,0,0,0.08);
            text-decoration: none;
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .navbar-category-link:hover {
            background: rgba(255,255,255,0.95);
            color: #667eea;
            transform: translateY(-1px);
        }

        .navbar-search-input {
            flex: 0 0 auto;
            min-width: 140px;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .navbar-search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }

        .navbar-search-btn {
            padding: 6px 12px;
            background: linear-gradient(135deg, #c7aaff 0%, #5981f1 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 11px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .navbar-search-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .navbar-menu {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: auto;
            position: relative;
            order: 2;
        }

        /* ハンバーガーメニュー */
        .hamburger {
            width: 54px;
            height: 54px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 7.5px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.5);
            border: none;
            border-radius: 6px;
            padding: 9px 12px;
            transition: all 0.3s ease;
        }

        .hamburger:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        .hamburger span {
            width: 100%;
            height: 4.5px;
            background: #333;
            border-radius: 2px;
            transition: all 0.3s ease;
            display: block;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(12px, 12px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(10.5px, -10.5px);
        }

        /* ドロップダウンメニュー */
        .menu-dropdown {
            position: fixed;
            top: 60px;
            right: 0;
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border-radius: 0 0 12px 12px;
            min-width: 220px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            z-index: 999;
        }

        .menu-dropdown.active {
            max-height: 500px;
        }

        .menu-dropdown a {
            display: block;
            padding: 14px 20px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
            font-family: 'LINESeedJP', sans-serif;
        }

        .menu-dropdown a.menu-home {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 20px;
        }

        .menu-dropdown a.menu-home img {
            height: 34px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .menu-dropdown a:last-child {
            border-bottom: none;
        }

        .menu-dropdown a:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            padding-left: 26px;
            color: #667eea;
        }
        
        @media (min-width: 768px) {
            .navbar-search {
                display: flex;
            }
            .navbar-categories {
                display: flex;
            }
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%, #5a67d8 100%);
            color: white;
            padding: 50px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(30px); }
        }
        
        .hero-section h1 {
            font-size: 42px;
            font-weight: 700;
            font-family: 'LINESeedJP', sans-serif;
            margin-bottom: 12px;
            line-height: 1.2;
            letter-spacing: -1px;
            position: relative;
            z-index: 1;
        }
        
        .hero-section p {
            font-size: 17px;
            opacity: 0.95;
            margin: 0;
            letter-spacing: 0.3px;
            position: relative;
            z-index: 1;
        }
        
        .container {
            padding: 0 12px !important;
            max-width: 100%;
        }

        .navbar > .container-fluid {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .main-container {
            padding: 30px 40px;
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }

        /* ===== TOP お知らせ ===== */
        .top-news {
            max-width: 1200px;
            margin: 8px auto 12px;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 8px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(102, 126, 234, 0.18);
        }

        .top-news-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 7px;
            margin-bottom: 6px;
        }

        .top-news-title {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .top-news-carousel {
            overflow: hidden;
            border-radius: 8px;
            cursor: grab;
            user-select: none;
            touch-action: pan-y;
        }

        .top-news-carousel.is-dragging {
            cursor: grabbing;
        }

        .top-news-track {
            display: flex;
            width: 100%;
            transform: translateX(0%);
            transition: transform 900ms ease;
            will-change: transform;
        }

        .top-news-slide {
            flex: 0 0 100%;
            width: 100%;
        }

        .top-news-slide .top-news-item {
            height: 100%;
        }

        .top-news-item {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 7px;
            padding: 7px 7px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .top-news-item.is-important {
            border-color: rgba(220, 53, 69, 0.35);
            box-shadow: 0 10px 24px rgba(220, 53, 69, 0.12);
        }

        .top-news-item-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 4px;
        }

        .top-news-item-title {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 800;
            letter-spacing: -0.2px;
        }

        .top-news-badge {
            display: inline-flex;
            align-items: center;
            padding: 1px 6px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            background: rgba(220, 53, 69, 0.12);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.28);
        }

        .top-news-item-date {
            color: rgba(0, 0, 0, 0.55);
            white-space: nowrap;
        }

        .top-news-item-body {
            color: rgba(0, 0, 0, 0.78);
            line-height: 1.6;
            font-size: 12px;
            white-space: normal;
        }

        .search-container {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }

        .search-form {
            width: 100%;
        }

        .search-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .search-input::placeholder {
            color: #aaa;
        }

        .search-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }

        .search-btn:active {
            transform: translateY(0);
        }

        .reset-btn {
            padding: 12px 20px;
            background: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .reset-btn:hover {
            background: #e0e0e0;
        }

        .section-title {
            font-size: 26px;
            font-weight: 700;
            font-family: 'LINESeedJP', sans-serif;
            margin: 24px 0 20px 0;
            padding: 0;
            color: #2c3e50;
            letter-spacing: -0.5px;
        }

        .section-title a {
            visibility: hidden;
            width: 0;
        }

        /* ===== カテゴリーセクション ===== */
        .category-section {
            margin-bottom: 40px;
        }

        .category-title {
            font-size: 36px;
            font-weight: 700;
            font-family: 'LINESeedJP', sans-serif;
            margin: 0 0 16px 0;
            padding: 12px 0 8px 0;
            color: #f1f1f3;
            letter-spacing: -0.5px;
            border-bottom: 3px solid rgba(102, 126, 234, 0.2);
            display: inline-block;
            text-shadow: 0 2px 4px rgba(2, 8, 34, 0.25), 0 4px 8px rgba(22, 4, 39, 0.15);
        }

        /* ===== 音源リスト（テーブル行形式） ===== */
        .sounds-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 30px;
            padding-left: 10mm;
            padding-right: 10mm;
        }

        .sound-row {
            display: grid;
            /* title列を約0.7倍に縮小（余りは中央へ） */
            /* controls列を約0.8倍に縮小（余りは中央へ） */
            grid-template-columns: 20% 72% 8%;
            gap: 12px;
            align-items: flex-start;
            background: white;
            border-radius: 12px;
            padding: 6px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .sound-row::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .sound-row:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0,0,0,0.12);
        }

        .sound-row:hover::before {
            transform: scaleX(1);
        }

        .sound-row-title {
            display: flex;
            align-items: center;
            align-self: center;
            min-height: 10px;
        }

        .title-text {
            font-size: 52px;
            font-weight: 600;
            color: #585f66;
            word-break: break-word;
            font-family: 'LINESeedJP', sans-serif;
            line-height: 1.1;
        }

        /* 中央セクション（タグと説明） */
        .sound-row-middle {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .tags-section {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .tag-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.1));
            color: #667eea;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid rgba(102, 126, 234, 0.2);
            font-family: 'Inter', sans-serif;
        }

        .description-section {
            font-size: 13px;
            color: #666;
            line-height: 1.3;
            font-family: 'LINESeedJP', sans-serif;
        }

        /* 右側セクション（試聴とDL） */
        .sound-row-controls {
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: stretch;
        }

        .sound-row-player {
            display: flex;
            align-items: center;
            flex: 1;
        }

        /* ===== カスタムオーディオプレイヤー ===== */
        .audio-player {
            display: none;
        }

        .custom-player {
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
            border-radius: 8px;
            padding: 6px 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .custom-player:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }

        .play-btn {
            width: 22px;
            height: 22px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            flex-shrink: 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }

        .play-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .play-btn:active {
            transform: scale(0.92);
        }

        .progress-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .progress-bar-wrapper {
            width: 100%;
            height: 3px;
            background: rgba(102, 126, 234, 0.2);
            border-radius: 2px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            width: 0%;
            transition: width 0.1s linear;
            position: relative;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            right: -5px;
            top: 50%;
            transform: translateY(-50%);
            width: 10px;
            height: 10px;
            background: #667eea;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.4);
        }

        .time-display {
            font-size: 9px;
            color: #666;
            font-weight: 500;
            font-family: 'Inter', monospace;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .volume-control {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .volume-icon {
            font-size: 11px;
            color: #667eea;
        }

        .volume-slider {
            width: 32px;
            height: 3px;
            cursor: pointer;
            accent-color: #667eea;
            -webkit-appearance: none;
            appearance: none;
            background: rgba(102, 126, 234, 0.2);
            border-radius: 2px;
            outline: none;
        }

        .volume-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        }

        .volume-slider::-moz-range-thumb {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        }

        .sound-row-download {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-size {
            font-size: 7px;
            color: #95a5a6;
            font-weight: 500;
        }

        .download-btn {
            width: 100%;
            padding: 4px 8px;
            background: linear-gradient(135deg, #c7aaff 0%, #5981f1 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 11px;
            min-height: 22px;
            font-family: 'Inter', sans-serif;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }

        .download-btn:active {
            transform: translateY(0);
        }
        
        .no-data {
            text-align: center;
            padding: 80px 20px;
            color: #7f8c8d;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .no-data p {
            font-size: 18px;
            margin: 0;
            font-weight: 500;
        }
        
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 15px;
            text-align: center;
            margin-top: auto;
            font-size: 14px;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.1);
        }
        
        footer p {
            margin: 8px 0;
            opacity: 0.9;
        }

        /* ***** タブレット対応（md以上） ***** */
        @media (min-width: 768px) {
            .hero-section {
                padding: 70px 30px;
            }
            
            .hero-section h1 {
                font-size: 52px;
                margin-bottom: 14px;
            }
            
            .hero-section p {
                font-size: 18px;
            }

            .brand-name-img {
                height: 45px;
            }

            .sounds-grid {
                grid-template-columns: 1fr 1fr;
                gap: 22px;
            }

            .sound-row {
                /* title列を約0.7倍に縮小（余りは中央へ） */
                /* controls列を約0.8倍に縮小（余りは中央へ） */
                grid-template-columns: 9fr 4.2fr 0.8fr;
                padding: 5px 6px;
                gap: 6px;
            }

            .custom-player {
                min-width: 144px;
                padding: 6px 8px;
                gap: 6px;
            }

            .play-btn {
                width: 26px;
                height: 26px;
                font-size: 11px;
            }

            .volume-slider {
                width: 40px;
            }
            
            .main-container {
                padding: 40px 30px;
            }
            
            .container {
                padding: 0 15px !important;
            }

            .search-wrapper {
                gap: 12px;
            }

            .search-input {
                padding: 13px 18px;
                font-size: 16px;
            }

            .search-btn {
                padding: 13px 28px;
                font-size: 16px;
            }

            .reset-btn {
                padding: 13px 24px;
                font-size: 16px;
            }
            
            .section-title {
                font-size: 30px;
                margin: 28px 0 22px 0;
            }

            .category-title {
                font-size: 26px;
                margin: 32px 0 20px 0;
            }
        }

        /* ***** デスクトップ対応（lg以上） ***** */
        @media (min-width: 992px) {
            .main-container {
                padding: 50px 60px;
            }

            .hero-section {
                padding: 80px 40px;
            }

            .hero-section h1 {
                font-size: 56px;
            }

            .brand-name-img {
                height: 55px;
            }

            .sound-row {
                /* title列を約0.7倍に縮小（余りは中央へ） */
                /* controls列を約0.8倍に縮小（余りは中央へ） */
                grid-template-columns: 5fr 4.2fr 0.8fr;
                padding: 6px 9px;
                gap: 8px;
            }

            .custom-player {
                min-width: 160px;
                padding: 8px 9px;
                gap: 8px;
            }

            .play-btn {
                width: 29px;
                height: 29px;
                font-size: 13px;
            }

            .progress-bar-wrapper {
                height: 4px;
            }

            .time-display {
                font-size: 10px;
            }

            .volume-slider {
                width: 48px;
            }

            .title-text {
                font-size: 20.7px;
            }

            /* PC版はタグ・説明を折り返さない（はみ出しは省略） */
            .sound-row-middle {
                min-width: 0;
            }

            .tags-section {
                flex-wrap: nowrap;
                overflow: hidden;
            }

            .tag-badge {
                white-space: nowrap;
            }

            .description-section {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .category-title {
                font-size: 28px;
                margin: 40px 0 24px 0;
            }
        }
    </style>
    <script>
        // ハンバーガーメニュー機能
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const menuDropdown = document.getElementById('menuDropdown');

            // メニュー開閉
            hamburgerBtn.addEventListener('click', function() {
                hamburgerBtn.classList.toggle('active');
                menuDropdown.classList.toggle('active');
            });

            // メニュー項目クリック時にメニューを閉じる
            const menuLinks = menuDropdown.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    hamburgerBtn.classList.remove('active');
                    menuDropdown.classList.remove('active');
                });
            });

            // ページの他の場所をクリックしたらメニューを閉じる
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar-menu') && !event.target.closest('.hamburger')) {
                    hamburgerBtn.classList.remove('active');
                    menuDropdown.classList.remove('active');
                }
            });

            // ===== カスタムオーディオプレイヤー機能 =====
            const players = document.querySelectorAll('.custom-player');
            
            players.forEach(playerWrapper => {
                const playBtn = playerWrapper.querySelector('.play-btn');
                const audio = playerWrapper.querySelector('.audio-player');
                const progressBar = playerWrapper.querySelector('.progress-bar');
                const progressBarWrapper = playerWrapper.querySelector('.progress-bar-wrapper');
                const timeDisplay = playerWrapper.querySelector('.time-display');
                const volumeSlider = playerWrapper.querySelector('.volume-slider');

                if (!audio) return;

                // 再生/一時停止
                playBtn.addEventListener('click', function() {
                    if (audio.paused) {
                        audio.play();
                        playBtn.textContent = '⏸';
                    } else {
                        audio.pause();
                        playBtn.textContent = '▶';
                    }
                });

                // 再生中の更新
                audio.addEventListener('timeupdate', function() {
                    const percent = (audio.currentTime / audio.duration) * 100;
                    progressBar.style.width = percent + '%';
                    updateTimeDisplay();
                });

                // メタデータ読み込み
                audio.addEventListener('loadedmetadata', function() {
                    updateTimeDisplay();
                });

                // 再生終了
                audio.addEventListener('ended', function() {
                    playBtn.textContent = '▶';
                });

                // プログレスバークリック
                progressBarWrapper.addEventListener('click', function(e) {
                    const rect = progressBarWrapper.getBoundingClientRect();
                    const percent = (e.clientX - rect.left) / rect.width;
                    audio.currentTime = percent * audio.duration;
                });

                // ボリューム調整
                if (volumeSlider) {
                    volumeSlider.addEventListener('input', function() {
                        audio.volume = this.value / 100;
                    });
                    audio.volume = volumeSlider.value / 100;
                }

                // 時間表示の更新
                function updateTimeDisplay() {
                    const formatTime = (time) => {
                        if (isNaN(time)) return '0:00';
                        const minutes = Math.floor(time / 60);
                        const seconds = Math.floor(time % 60);
                        return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                    };

                    const current = formatTime(audio.currentTime);
                    const duration = formatTime(audio.duration);
                    timeDisplay.textContent = current + ' / ' + duration;
                }

                // 初期状態
                playBtn.textContent = '▶';
                updateTimeDisplay();
            });
        });
    </script>
</head>
<body>
    <!-- ナビバー -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <div class="navbar-start">
                <a class="navbar-brand fw-bold" href="/">
                    <!-- <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Logo" class="navbar-logo"> -->
                    <img src="<?php echo SITE_URL; ?>/assets/images/brand-name.png" alt="<?php echo APP_NAME; ?>" class="brand-name-img">
                </a>
            </div>
            <?php if (empty($hide_nav_search) || $hide_nav_search === false): ?>
                <form method="GET" class="navbar-search">
                    <input type="text" name="search" class="navbar-search-input" 
                           placeholder="🔍 検索..." 
                           value="<?php echo isset($navbar_search) ? esc($navbar_search) : ''; ?>">
                    <button type="submit" class="navbar-search-btn">検索</button>
                </form>
                <?php if (!empty($navbar_categories) && is_array($navbar_categories)): ?>
                    <div class="navbar-categories" aria-label="カテゴリ一覧">
                        <div class="navbar-categories-list">
                            <?php foreach ($navbar_categories as $cat): ?>
                                <a class="navbar-category-link"
                                   href="<?php echo SITE_URL; ?>/#category-<?php echo esc($cat['id']); ?>">
                                    <?php echo esc($cat['name']); ?>
                                </a>
                            <?php endforeach; ?>
                            <a class="navbar-category-link"
                               href="<?php echo SITE_URL; ?>/#category-uncategorized">その他</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="navbar-menu">
                <button class="hamburger" id="hamburgerBtn" aria-label="メニュー">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="menu-dropdown" id="menuDropdown">
                    <a class="menu-home" href="<?php echo SITE_URL; ?>/">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="TOP">
                    </a>
                    <a href="<?php echo SITE_URL; ?>/details/deteils.php">利用規約</a>
                    <a href="#about-yuya">効果音リクエスト/お問い合わせ</a>
                    <a href="<?php echo SITE_URL; ?>/creator\creator.php">製作者について</a>
                    <a href="<?php echo SITE_URL; ?>/privacy/privacy.php">プライバシーポリシー</a>
                    <a href="/portfolio-php/admin/">管理人</a>
                </div>
            </div>
            </div>
        </div>
    </nav>
