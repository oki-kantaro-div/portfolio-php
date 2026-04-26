<?php
// ナビバーとサイドバーは不要なため、header.phpは読み込まない
require_once '../config.php';
require_once '../functions.php';

// すでにログイン状態なら dashboard へリダイレクト
if (isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$error_message = '';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面ログイン - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-container h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
            font-size: 28px;
            font-weight: bold;
        }
        .login-container .subtitle {
            text-align: center;
            color: #999;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 10px 12px;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
        .alert {
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>staff only</h1>
        <h2 class="h5 text-center mb-2">管理画面</h2>
        <p class="subtitle"><?php echo APP_NAME; ?></p>

        <?php
        if ($error = getSessionMessage('error')) {
            echo '<div class="alert alert-danger">' . esc($error) . '</div>';
        }
        ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username" class="form-label">ユーザーID</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">パスワード</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <button type="submit" class="btn btn-login">ログイン</button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted small">
            初期登録が必要な場合は、<br>
            PHPMyAdmin等でadmin_usersテーブルにデータを挿入してください。
        </p>
    </div>
</body>
</html>
