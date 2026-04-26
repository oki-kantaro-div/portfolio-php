<?php
require_once '../config.php';
require_once '../functions.php';

// ログイン確認
requireLogin();

global $db;

$page_title = 'カテゴリー管理';

// アクション処理
$action = $_GET['action'] ?? '';
$success_message = '';
$error_message = '';

// ===== カテゴリー作成 =====
if ($_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    if (empty($name)) {
        $error_message = 'カテゴリー名を入力してください';
    } else {
        $sql = "INSERT INTO categories (name, display_order) VALUES (?, ?)";
        if ($db->execute($sql, [$name, $display_order])) {
            $success_message = 'カテゴリーを作成しました';
        } else {
            $error_message = 'カテゴリーの作成に失敗しました';
        }
    }
}

// ===== カテゴリー更新 =====
if ($_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    if (empty($name)) {
        $error_message = 'カテゴリー名を入力してください';
    } else {
        $sql = "UPDATE categories SET name = ?, display_order = ? WHERE id = ?";
        if ($db->execute($sql, [$name, $display_order, $id])) {
            $success_message = 'カテゴリーを更新しました';
        } else {
            $error_message = 'カテゴリーの更新に失敗しました';
        }
    }
}

// ===== カテゴリー削除 =====
if ($action === 'delete') {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM categories WHERE id = ?";
    if ($db->execute($sql, [$id])) {
        $success_message = 'カテゴリーを削除しました';
    } else {
        $error_message = 'カテゴリーの削除に失敗しました';
    }
}

// 全カテゴリー取得
$categories = $db->select("SELECT * FROM categories ORDER BY display_order ASC, created_at DESC");

require_once 'header.php';
?>

<div class="admin-container">
    <h1>📁 カテゴリー管理</h1>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- カテゴリー作成フォーム -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">新規カテゴリー作成</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">カテゴリー名 *</label>
                        <input type="text" name="name" class="form-control" placeholder="例：環境音①" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">表示順序</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">作成</button>
            </form>
        </div>
    </div>
    
    <!-- カテゴリー一覧 -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">カテゴリー一覧</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>カテゴリー名</th>
                        <th>表示順序</th>
                        <th>音源数</th>
                        <th>作成日</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">カテゴリーが登録されていません</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <?php
                            $count_sql = "SELECT COUNT(*) as cnt FROM sounds WHERE category_id = ?";
                            $count_result = $db->select($count_sql, [$category['id']]);
                            $sound_count = $count_result[0]['cnt'] ?? 0;
                            ?>
                            <tr>
                                <td>#<?php echo $category['id']; ?></td>
                                <td><?php echo esc($category['name']); ?></td>
                                <td><?php echo $category['display_order']; ?></td>
                                <td><span class="badge bg-info"><?php echo $sound_count; ?></span></td>
                                <td><?php echo date('Y-m-d', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <a href="category-edit.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">編集</a>
                                    <a href="?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('本当に削除しますか？')">削除</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
