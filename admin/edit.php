<?php
require_once '../config.php';
require_once '../functions.php';

// ログインチェック
requireLogin();

$page_title = '音源編集';

// DB接続
global $db;

// IDパラメータ取得
$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (empty($id) || !is_numeric($id)) {
    setErrorMessage('無効なリクエストです。');
    redirect(ADMIN_URL . '/dashboard.php');
}

// 音源データ取得
$sound = $db->selectOne('SELECT * FROM sounds WHERE id = ?', [$id]);
if (!$sound) {
    setErrorMessage('音源が見つかりません。');
    redirect(ADMIN_URL . '/dashboard.php');
}

// POSTでの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークン検証
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setErrorMessage('セキュリティトークンが無効です。');
        redirect(ADMIN_URL . '/edit.php?id=' . $id);
    }

    // 入力値取得
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $tags_input = $_POST['tags'] ?? '';
    $category_id = (int)($_POST['category_id'] ?? 0);
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    // バリデーション
    if (empty($title)) {
        setErrorMessage('タイトルは必須です。');
        redirect(ADMIN_URL . '/edit.php?id=' . $id);
    }

    // ファイル再アップロード（任意）
    $filename = $sound['filename'];
    $original_name = $sound['original_name'];
    $file_size = $sound['file_size'];

    if (!empty($_FILES['audio_file']['name'])) {
        // ファイル検証
        $validation = validateAudioFile($_FILES['audio_file']);
        if (!$validation['valid']) {
            setErrorMessage($validation['error']);
            redirect(ADMIN_URL . '/edit.php?id=' . $id);
        }

        // 新しいファイルをアップロード
        $upload_result = uploadAudioFile($_FILES['audio_file']);
        if (!$upload_result['success']) {
            setErrorMessage($upload_result['error']);
            redirect(ADMIN_URL . '/edit.php?id=' . $id);
        }

        // 古いファイルを削除
        deleteAudioFile($sound['filename']);

        // 新しい情報に更新
        $filename = $upload_result['filename'];
        $original_name = basename($_FILES['audio_file']['name']);
        $file_size = $_FILES['audio_file']['size'];
    }

    // DB更新
    $sql = 'UPDATE sounds SET title = ?, description = ?, category_id = ?, is_public = ?, filename = ?, original_name = ?, file_size = ? WHERE id = ?';
    
    if ($db->execute($sql, [$title, $description, $category_id ?: null, $is_public, $filename, $original_name, $file_size, $id])) {
        // 既存のタグを削除
        $db->execute('DELETE FROM tags WHERE sound_id = ?', [$id]);
        
        // 新しいタグを保存
        if (!empty($tags_input)) {
            $tags_array = array_filter(array_map('trim', explode(' ', $tags_input)));
            $tag_errors = [];
            foreach ($tags_array as $tag_name) {
                // # を削除
                $tag_name = ltrim($tag_name, '#');
                if (!empty($tag_name)) {
                    if (!$db->execute('INSERT INTO tags (sound_id, tag_name) VALUES (?, ?)', [$id, $tag_name])) {
                        $tag_errors[] = $db->getError();
                    }
                }
            }
            // タグ保存時のエラーがあればログに出力
            if (!empty($tag_errors)) {
                error_log('Tag save errors: ' . implode(' | ', $tag_errors));
            }
        }
        
        setSuccessMessage('音源を更新しました。');
        redirect(ADMIN_URL . '/dashboard.php');
    } else {
        setErrorMessage('更新に失敗しました。');
        redirect(ADMIN_URL . '/edit.php?id=' . $id);
    }
}

// 初期値
$error_message = getSessionMessage('error');
$csrf_token = generateCSRFToken();

// 既存のタグを取得
$existing_tags = $db->select('SELECT tag_name FROM tags WHERE sound_id = ? ORDER BY created_at ASC', [$id]);
$tags_string = implode(' ', array_map(function($tag) { return '#' . $tag['tag_name']; }, $existing_tags));

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
        <h2 class="mb-4">✏️ 音源編集</h2>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <!-- 現在のファイル情報 -->
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong>📁 現在のファイル</strong><br>
                        <span class="text-muted"><?php echo esc($sound['original_name']); ?></span><br>
                        <small class="text-muted d-block mt-2">
                            サイズ: <?php echo formatFileSize($sound['file_size']); ?> | 
                            登録日: <?php echo formatDateTime($sound['uploaded_at']); ?>
                        </small>
                    </div>

                    <!-- ファイル再アップロード（任意） -->
                    <div class="mb-3">
                        <label for="audio_file" class="form-label">
                            MP3ファイル（再アップロード、任意）
                        </label>
                        <div class="input-group">
                            <input type="file" id="audio_file" name="audio_file" class="form-control" 
                                   accept=".mp3,audio/mpeg">
                            <span class="input-group-text text-nowrap">
                                <small class="text-muted">最大10MB</small>
                            </span>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            ℹ️ ファイルを選択すると、古いファイルが削除され新しいファイルに置き換わります。
                        </small>
                    </div>

                    <!-- タイトル -->
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            タイトル <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo esc($sound['title']); ?>" required>
                    </div>

                    <!-- 大カテゴリー選択 -->
                    <div class="mb-3">
                        <label for="parent_category_id" class="form-label">
                            大カテゴリー（任意）
                        </label>
                        <select id="parent_category_id" name="parent_category_id" class="form-control">
                            <option value="">-- 大カテゴリーを選択 --</option>
                            <?php
                            $parent_categories = $db->select("SELECT id, name FROM categories ORDER BY display_order ASC");
                            foreach ($parent_categories as $pcat):
                            ?>
                                <option value="<?php echo $pcat['id']; ?>">
                                    <?php echo esc($pcat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 小カテゴリー選択 -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">
                            小カテゴリー（任意）
                        </label>
                        <select id="category_id" name="category_id" class="form-control">
                            <option value="">-- 小カテゴリーなし --</option>
                            <?php
                            // 現在のカテゴリーの大カテゴリーを取得
                            if ($sound['category_id']) {
                                $current_sub = $db->selectOne(
                                    "SELECT parent_id FROM sub_categories WHERE id = ?",
                                    [$sound['category_id']]
                                );
                                if ($current_sub) {
                                    $subs = $db->select(
                                        "SELECT id, name FROM sub_categories WHERE parent_id = ? ORDER BY display_order ASC",
                                        [$current_sub['parent_id']]
                                    );
                                    foreach ($subs as $sub):
                                    ?>
                                        <option value="<?php echo $sub['id']; ?>" 
                                                <?php echo ($sound['category_id'] == $sub['id']) ? 'selected' : ''; ?>>
                                            <?php echo esc($sub['name']); ?>
                                        </option>
                                    <?php
                                    endforeach;
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- JavaScriptで小カテゴリーを動的ロード -->
                    <script>
                    (function() {
                        const subCategoriesData = <?php
                            $all_sub = $db->select("SELECT id, parent_id, name FROM sub_categories ORDER BY parent_id ASC, display_order ASC");
                            echo json_encode(
                                array_reduce($all_sub, function($acc, $item) {
                                    $parent = $item['parent_id'];
                                    if (!isset($acc[$parent])) $acc[$parent] = [];
                                    $acc[$parent][] = $item;
                                    return $acc;
                                }, [])
                            );
                        ?>;

                        const parentSelect = document.getElementById('parent_category_id');
                        const categorySelect = document.getElementById('category_id');

                        // 初期化：現在のカテゴリーから大カテゴリーを取得して選択
                        <?php if ($sound['category_id']): ?>
                            const currentSub = <?php
                                $current_sub = $db->selectOne("SELECT parent_id FROM sub_categories WHERE id = ?", [$sound['category_id']]);
                                echo $current_sub ? $current_sub['parent_id'] : '""';
                            ?>;
                            if (currentSub) {
                                parentSelect.value = currentSub;
                            }
                        <?php endif; ?>

                        parentSelect.addEventListener('change', function() {
                            const parentId = this.value;
                            categorySelect.innerHTML = '<option value="">-- 小カテゴリーなし --</option>';
                            
                            if (parentId && subCategoriesData[parentId]) {
                                subCategoriesData[parentId].forEach(function(sub) {
                                    const option = document.createElement('option');
                                    option.value = sub.id;
                                    option.textContent = sub.name;
                                    categorySelect.appendChild(option);
                                });
                            }
                        });
                    })();
                    </script>

                    <!-- 説明 -->
                    <div class="mb-3">
                        <label for="description" class="form-label">説明（任意）</label>
                        <textarea id="description" name="description" class="form-control" 
                                  rows="4"><?php echo esc($sound['description']); ?></textarea>
                    </div>

                    <!-- タグ -->
                    <div class="mb-3">
                        <label for="tags" class="form-label">タグ（任意、スペース区切り）</label>
                        <input type="text" id="tags" name="tags" class="form-control" 
                               value="<?php echo esc($tags_string); ?>"
                               placeholder="例：#環境音 #雨 #自然">
                        <small class="form-text text-muted d-block mt-2">
                            💡 複数のタグをスペースで区切って入力してください。#は自動的に削除されます。
                        </small>
                    </div>

                    <!-- 公開フラグ -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_public" 
                                   name="is_public" <?php echo $sound['is_public'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_public">
                                📢 公開状態（チェックで公開、外すと非公開）
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="id" value="<?php echo $sound['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <!-- ボタン -->
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-md-grow-0">
                            💾 更新
                        </button>
                        <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="btn btn-secondary flex-md-grow-0">
                            ❌ キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
