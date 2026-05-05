<?php
require_once '../config.php';
require_once '../functions.php';

requireLogin();

$page_title = 'お知らせ新規作成';
global $db;

// POSTでの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setErrorMessage('セキュリティトークンが無効です。');
        redirect(ADMIN_URL . '/news-create.php');
    }

    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $is_important = isset($_POST['is_important']) ? 1 : 0;
    $publish_start = trim($_POST['publish_start'] ?? '');
    $publish_end = trim($_POST['publish_end'] ?? '');
    $publish_end = ($publish_end === '') ? null : $publish_end;

    $errors = [];
    if ($title === '') $errors[] = 'タイトルは必須です。';
    if ($body === '') $errors[] = '本文は必須です。';
    if ($publish_start === '') $errors[] = '公開開始日は必須です。';

    if ($publish_start !== '' && strtotime($publish_start) === false) {
        $errors[] = '公開開始日の形式が不正です。';
    }
    if ($publish_end !== null && $publish_end !== '' && strtotime($publish_end) === false) {
        $errors[] = '公開終了日の形式が不正です。';
    }
    if ($publish_end !== null && $publish_start !== '' && strtotime($publish_start) !== false && strtotime($publish_end) !== false) {
        if (strtotime($publish_end) < strtotime($publish_start)) {
            $errors[] = '公開終了日は公開開始日以降にしてください。';
        }
    }

    if (!empty($errors)) {
        setErrorMessage(implode('<br>', $errors));
        redirect(ADMIN_URL . '/news-create.php');
    }

    $sql = "INSERT INTO NewsRelease (title, body, is_important, publish_start, publish_end)
            VALUES (?, ?, ?, ?, ?)";

    if ($db->execute($sql, [$title, $body, $is_important, $publish_start, $publish_end])) {
        setSuccessMessage('お知らせを作成しました。');
        redirect(ADMIN_URL . '/news.php');
    }

    setErrorMessage('作成に失敗しました。');
    redirect(ADMIN_URL . '/news-create.php');
}

$error_message = getSessionMessage('error');
$csrf_token = generateCSRFToken();

// 初期値（フォーム表示用）
$default_start = date('Y-m-d\TH:i');

require_once 'header.php';
?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2">
        <h2 class="mb-4">➕ お知らせ新規作成</h2>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">タイトル <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">本文 <span class="text-danger">*</span></label>
                        <textarea id="body" name="body" class="form-control" rows="6" required></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_important" name="is_important">
                            <label class="form-check-label" for="is_important">重要なお知らせ（強調表示）</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="publish_start" class="form-label">公開開始日 <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="publish_start" name="publish_start" class="form-control"
                                   value="<?php echo esc($default_start); ?>" required>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="publish_end" class="form-label">公開終了日（任意）</label>
                            <input type="datetime-local" id="publish_end" name="publish_end" class="form-control">
                            <small class="text-muted">※ 未入力の場合は「終了なし」で公開できます</small>
                        </div>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-md-grow-0">✅ 作成</button>
                        <a href="<?php echo ADMIN_URL; ?>/news.php" class="btn btn-secondary flex-md-grow-0">❌ 戻る</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

