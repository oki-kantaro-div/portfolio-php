<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? esc($page_title) . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.75rem 1rem;
        }
        .navbar-brand {
            font-size: 1.1rem !important;
        }
        .sidebar {
            background-color: #fff;
            border-right: 1px solid #dee2e6;
            padding: 15px 10px;
            min-height: 100vh;
        }
        .sidebar h6 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.8rem;
        }
        .sidebar a {
            color: #495057;
            text-decoration: none;
            display: block;
            padding: 8px 10px;
            margin-bottom: 5px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .sidebar a:hover {
            background-color: #e9ecef;
            color: #212529;
        }
        .sidebar a.active {
            background-color: #0d6efd;
            color: #fff;
        }
        .main-content {
            padding: 20px 15px;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 6px;
        }
        .no-data {
            text-align: center;
            padding: 40px 15px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .table {
            font-size: 0.85rem;
        }
        
        .table th, .table td {
            padding: 0.5rem;
            vertical-align: middle;
        }
        
        .btn-group-sm .btn {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
        }
        
        h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        .card {
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .alert {
            padding: 10px 12px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* ***** タブレット以上（md） ***** */
        @media (min-width: 768px) {
            .sidebar {
                padding: 20px;
            }
            
            .sidebar a {
                padding: 8px 12px;
                font-size: 0.95rem;
            }
            
            .main-content {
                padding: 30px;
            }
            
            h2 {
                font-size: 28px;
                margin-bottom: 20px;
            }
            
            .table {
                font-size: 0.95rem;
            }
            
            .table th, .table td {
                padding: 0.75rem;
            }
            
            .status-badge {
                font-size: 0.85rem;
                padding: 4px 8px;
            }
        }

        /* ***** モバイル横向き（sm） ***** */
        @media (max-width: 575px) {
            .col-md-2.sidebar {
                margin-bottom: 10px;
            }
            
            .navbar-brand {
                font-size: 1rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- トップナビバー -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo ADMIN_URL; ?>/dashboard.php">
                🎵 <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <?php 
                            if (isset($_SESSION['admin_id'])) {
                                echo '管理者ID: <strong>' . esc($_SESSION['admin_id']) . '</strong>';
                            }
                            ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/logout.php">ログアウト</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- サイドバー -->
            <div class="col-md-2 sidebar">
                <h6>メニュー</h6>
                <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    📋 音源一覧
                </a>
                <a href="<?php echo ADMIN_URL; ?>/upload.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'upload.php') ? 'active' : ''; ?>">
                    ⬆️ 新規追加
                </a>
                <a href="<?php echo ADMIN_URL; ?>/news.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'news.php' || basename($_SERVER['PHP_SELF']) == 'news-create.php' || basename($_SERVER['PHP_SELF']) == 'news-edit.php') ? 'active' : ''; ?>">
                    📰 お知らせ管理
                </a>
            </div>

            <!-- メインコンテンツ -->
            <div class="col-md-10 main-content">
