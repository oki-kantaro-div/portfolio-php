<?php
require_once '../config.php';
require_once '../functions.php';

// ログインチェック
requireLogin();

$page_title = '新規音源追加';

// DB接続
global $db;

// POSTでの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークン検証
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setErrorMessage('セキュリティトークンが無効です。');
        redirect(ADMIN_URL . '/upload.php');
    }

    // 入力値取得
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $tags_input = $_POST['tags'] ?? '';
    $category_id = (int)($_POST['category_id'] ?? 0);
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    // バリデーション
    $errors = [];
    if (empty($title)) {
        $errors[] = 'タイトルは必須です。';
    }
    if (empty($_FILES['audio_file']['name'])) {
        $errors[] = 'ファイルを選択してください。';
    }

    if (!empty($errors)) {
        setErrorMessage(implode('<br>', $errors));
        redirect(ADMIN_URL . '/upload.php');
    }

    // ファイルアップロード
    $upload_result = uploadAudioFile($_FILES['audio_file']);
    if (!$upload_result['success']) {
        setErrorMessage($upload_result['error']);
        redirect(ADMIN_URL . '/upload.php');
    }

    // DB保存
    $filename = $upload_result['filename'];
    $original_name = basename($_FILES['audio_file']['name']);
    $file_size = $_FILES['audio_file']['size'];

    $sql = 'INSERT INTO sounds (filename, original_name, title, description, category_id, is_public, file_size) 
            VALUES (?, ?, ?, ?, ?, ?, ?)';
    
    if ($db->execute($sql, [$filename, $original_name, $title, $description, $category_id ?: null, $is_public, $file_size])) {
        // タグを保存
        $sound_id = $db->lastInsertId();
        
        // DEBUG: IDを確認
        if (empty($sound_id)) {
            error_log('ERROR: sound_id is empty after INSERT');
        } else {
            error_log('DEBUG: sound_id = ' . $sound_id);
        }
        
        if (!empty($tags_input) && !empty($sound_id)) {
            $tags_array = array_filter(array_map('trim', explode(' ', $tags_input)));
            error_log('DEBUG: tags_array = ' . json_encode($tags_array));
            
            $tag_errors = [];
            foreach ($tags_array as $tag_name) {
                // # を削除
                $tag_name = ltrim($tag_name, '#');
                if (!empty($tag_name)) {
                    $tag_insert_result = $db->execute('INSERT INTO tags (sound_id, tag_name) VALUES (?, ?)', [$sound_id, $tag_name]);
                    if (!$tag_insert_result) {
                        $tag_error = $db->getError();
                        error_log('ERROR: Tag insert failed for "' . $tag_name . '": ' . $tag_error);
                        $tag_errors[] = $tag_error;
                    } else {
                        error_log('DEBUG: Tag "' . $tag_name . '" inserted successfully');
                    }
                }
            }
            // タグ保存時のエラーがあればログに出力（本番環境ではログファイルに記録推奨）
            if (!empty($tag_errors)) {
                error_log('Tag save errors: ' . implode(' | ', $tag_errors));
            }
        } else {
            error_log('DEBUG: tags_input is empty or sound_id is empty');
        }
        setSuccessMessage('音源を追加しました。');
        redirect(ADMIN_URL . '/dashboard.php');
    } else {
        // ファイルをロールバック
        deleteAudioFile($filename);
        setErrorMessage('データベースへの保存に失敗しました。');
        redirect(ADMIN_URL . '/upload.php');
    }
}

// 初期値
$error_message = getSessionMessage('error');
$csrf_token = generateCSRFToken();

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
        <h2 class="mb-4">⬆️ 新規音源追加</h2>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <!-- ファイル選択 -->
                    <div class="mb-3">
                        <label for="audio_file" class="form-label">
                            MP3ファイル <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="file" id="audio_file" name="audio_file" class="form-control" 
                                   accept=".mp3,audio/mpeg" required>
                            <span class="input-group-text text-nowrap">
                                <small class="text-muted">最大10MB</small>
                            </span>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            📝 ファイル情報：<span id="file-info">選択してください</span>
                        </small>
                    </div>

                    <!-- タイトル -->
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            タイトル <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="title" name="title" class="form-control" 
                               placeholder="例：波の音" required>
                    </div>

                    <!-- カテゴリー -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">
                            カテゴリー（任意）
                        </label>
                        <select id="category_id" name="category_id" class="form-control">
                            <option value="">-- カテゴリーなし --</option>
                            <?php
                            $categories = $db->select("SELECT id, name FROM categories ORDER BY display_order ASC");
                            foreach ($categories as $cat):
                            ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo esc($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 説明 -->
                    <div class="mb-3">
                        <label for="description" class="form-label">説明（任意）</label>
                        <textarea id="description" name="description" class="form-control" 
                                  rows="4" placeholder="この音源について説明を入力..."></textarea>
                    </div>

                    <!-- タグ -->
                    <div class="mb-3">
                        <label for="tags" class="form-label">タグ（任意、スペース区切り）</label>
                        <input type="text" id="tags" name="tags" class="form-control" 
                               placeholder="例：#環境音 #雨 #自然">
                        <small class="form-text text-muted d-block mt-2">
                            💡 複数のタグをスペースで区切って入力してください。#は自動的に削除されます。
                        </small>
                    </div>

                    <!-- 公開フラグ -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_public" 
                                   name="is_public" checked>
                            <label class="form-check-label" for="is_public">
                                📢 公開状態（チェックで公開、外すと非公開）
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <!-- ボタン -->
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-md-grow-0">
                            ✅ 追加
                        </button>
                        <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="btn btn-secondary flex-md-grow-0">
                            ❌ キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 注意事項 -->
        <div class="alert alert-info mt-4" role="alert">
            <strong>⚠️ 注意事項</strong>
            <ul class="mb-0 mt-2 ps-3">
                <li>MP3ファイルのみアップロード可能です</li>
                <li>ファイルサイズは10MBまでです</li>
                <li>ファイル名は自動的にランダム化されます</li>
                <li>タイトルは必須です</li>
            </ul>
        </div>
    </div>
</div>

<script>
// ファイル選択時の情報表示
document.getElementById('audio_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        document.getElementById('file-info').textContent = 
            file.name + ' (' + sizeMB + ' MB)';
    }
});
</script>

<?php require_once 'footer.php'; ?>
