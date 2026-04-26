<?php
require_once '../config.php';
require_once '../functions.php';

// ログイン確認
requireLogin();

global $db;

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: category.php');
    exit;
}

// カテゴリー取得
$category = $db->selectOne("SELECT * FROM categories WHERE id = ?", [$id]);

if (!$category) {
    header('Location: category.php');
    exit;
}

$page_title = 'カテゴリー編集';

// 更新処理
$error_message = '';
$success_message = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    if (empty($name)) {
        $error_message = 'カテゴリー名を入力してください';
    } else {
        $sql = "UPDATE categories SET name = ?, display_order = ? WHERE id = ?";
        if ($db->execute($sql, [$name, $display_order, $id])) {
            $success_message = 'カテゴリーを更新しました';
            $category['name'] = $name;
            $category['display_order'] = $display_order;
        } else {
            $error_message = 'カテゴリーの更新に失敗しました';
        }
    }
}

require_once 'header.php';
?>

<div class="admin-container">
    <h1>✏️ カテゴリー編集</h1>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- 編集フォーム -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">編集内容</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">カテゴリー名 *</label>
                    <input type="text" name="name" class="form-control" value="<?php echo esc($category['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">表示順序</label>
                    <input type="number" name="display_order" class="form-control" value="<?php echo $category['display_order']; ?>">
                    <small class="text-muted">小さい番号ほど上に表示されます</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">作成日</label>
                    <input type="text" class="form-control" disabled value="<?php echo date('Y-m-d H:i:s', strtotime($category['created_at'])); ?>">
                </div>
                <button type="submit" class="btn btn-primary">更新</button>
                <a href="category.php" class="btn btn-secondary">キャンセル</a>
            </form>
        </div>
    </div>
    
    <!-- 所属音源 -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">このカテゴリーに所属する音源</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>ファイルサイズ</th>
                        <th>アップロード日</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sounds = $db->select(
                        "SELECT id, title, file_size, uploaded_at FROM sounds WHERE category_id = ? ORDER BY uploaded_at DESC",
                        [$id]
                    );
                    ?>
                    <?php if (empty($sounds)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">このカテゴリーに所属する音源はありません</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sounds as $sound): ?>
                            <tr>
                                <td>#<?php echo $sound['id']; ?></td>
                                <td><?php echo esc($sound['title']); ?></td>
                                <td><?php echo formatFileSize($sound['file_size']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($sound['uploaded_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
