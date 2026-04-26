<?php
require_once '../config.php';
require_once '../functions.php';

// ログインチェック
requireLogin();

$page_title = '音源一覧';

// DB接続
global $db;

// ===== カテゴリー新規追加処理 =====
if (($_POST['action'] ?? null) === 'add_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $category_order = (int)($_POST['category_order'] ?? 0);
    
    if (!empty($category_name)) {
        $sql = "INSERT INTO categories (name, display_order) VALUES (?, ?)";
        $db->execute($sql, [$category_name, $category_order]);
        
        // リダイレクト（キャッシュ対策）
        header('Location: dashboard.php?message=added');
        exit;
    }
}

// ===== カテゴリー削除処理 =====
if (($_GET['action'] ?? null) === 'delete_category') {
    $category_id = (int)$_GET['category_id'];
    
    // 関連する音源のcategory_idをNULLに更新
    $sql_update = "UPDATE sounds SET category_id = NULL WHERE category_id = ?";
    $db->execute($sql_update, [$category_id]);
    
    // カテゴリーを削除
    $sql_delete = "DELETE FROM categories WHERE id = ?";
    $db->execute($sql_delete, [$category_id]);
    
    // リダイレクト
    header('Location: dashboard.php?message=deleted');
    exit;
}

// 検索キーワード取得
$search = $_GET['search'] ?? '';
$search = trim($search);

// SQLクエリ構築
$sql = 'SELECT s.*, COALESCE(c.name, "（未分類）") as category_name FROM sounds s LEFT JOIN categories c ON s.category_id = c.id';
$params = [];

if (!empty($search)) {
    $sql .= ' WHERE s.title LIKE ?';
    $params = ['%' . $search . '%'];
}

$sql .= ' ORDER BY s.uploaded_at DESC LIMIT 100';

// データ取得
$sounds = $db->select($sql, $params);

// カテゴリー一覧取得
$sql_categories = "SELECT c.id, c.name, c.display_order, COUNT(s.id) as sound_count 
                   FROM categories c 
                   LEFT JOIN sounds s ON c.id = s.category_id
                   GROUP BY c.id, c.name, c.display_order
                   ORDER BY c.display_order ASC";
$categories = $db->select($sql_categories);

// messages 
$success_message = getSessionMessage('success');
$error_message = getSessionMessage('error');

// 削除メッセージ確認
if (($_GET['message'] ?? null) === 'deleted') {
    $success_message = '✅ カテゴリーを削除しました。関連する音源はカテゴリーなしに移動されました。';
}

// 追加メッセージ確認
if (($_GET['message'] ?? null) === 'added') {
    $success_message = '✅ 新しいカテゴリーを追加しました。';
}

// ページ用 token
$csrf_token = generateCSRFToken();

require_once 'header.php';
?>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo esc($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo esc($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ===== カテゴリー管理セクション ===== -->
<div class="card mb-5" style="border-left: 5px solid #667eea;">
    <div class="card-header bg-light">
        <h4 class="mb-0">🗂️ カテゴリー管理</h4>
    </div>
    <div class="card-body">
        <!-- 新規追加フォーム -->
        <div class="mb-4">
            <h5 style="margin-bottom: 15px;">新規カテゴリー追加</h5>
            <form method="POST" class="row g-3" style="max-width: 500px;">
                <input type="hidden" name="action" value="add_category">
                <div class="col-12">
                    <label for="category_name" class="form-label">カテゴリー名</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" 
                           placeholder="例: JPop, 洋楽, 楽器..." required>
                </div>
                <div class="col-12">
                    <label for="category_order" class="form-label">表示順序</label>
                    <input type="number" class="form-control" id="category_order" name="category_order" 
                           value="0" min="0">
                    <small class="text-muted">※ 小さい順に上から表示されます</small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">➕ 追加</button>
                </div>
            </form>
        </div>

        <hr>

        <!-- カテゴリー一覧 -->
        <h5 style="margin: 15px 0;">カテゴリー一覧</h5>
        <?php if (empty($categories)): ?>
            <p class="text-muted">カテゴリーがまだ登録されていません。</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>カテゴリー名</th>
                            <th>表示順序</th>
                            <th>音源数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><small>#<?php echo $cat['id']; ?></small></td>
                            <td><?php echo esc($cat['name']); ?></td>
                            <td><?php echo $cat['display_order']; ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $cat['sound_count']; ?></span>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/category-edit.php?id=<?php echo $cat['id']; ?>" 
                                   class="btn btn-sm btn-warning" title="編集">✏️</a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $cat['id']; ?>)" 
                                   class="btn btn-sm btn-danger" title="削除">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🎵 音源一覧</h2>
    <a href="<?php echo ADMIN_URL; ?>/upload.php" class="btn btn-primary">
        ⬆️ 新規追加
    </a>
</div>

<!-- 検索フォーム -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-9">
                <input type="text" name="search" class="form-control" 
                       placeholder="タイトルで検索..." 
                       value="<?php echo esc($search); ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary w-100">🔍 検索</button>
                <?php if (!empty($search)): ?>
                    <a href="dashboard.php" class="btn btn-light w-100 mt-2">リセット</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- 音源テーブル -->
<?php if (empty($sounds)): ?>
    <div class="no-data">
        <p>📭 音源がまだ登録されていません。</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>タイトル</th>
                    <th class="d-none d-md-table-cell">カテゴリー</th>
                    <th class="d-none d-md-table-cell">状態</th>
                    <th class="d-none d-lg-table-cell">ファイルサイズ</th>
                    <th class="d-none d-lg-table-cell">アップロード日時</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sounds as $sound): ?>
                    <tr>
                        <td>
                            <div><strong><?php echo esc($sound['title']); ?></strong></div>
                            <small class="text-muted d-block"><?php echo esc($sound['original_name']); ?></small>
                            <?php if (!$sound['is_public']): ?>
                                <span class="badge bg-secondary status-badge d-md-none">⚫ 非公開</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <span class="badge bg-info text-dark">📁 <?php echo esc($sound['category_name']); ?></span>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php if ($sound['is_public']): ?>
                                <span class="badge bg-success status-badge">🟢 公開</span>
                            <?php else: ?>
                                <span class="badge bg-secondary status-badge">⚫ 非公開</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <?php echo formatFileSize($sound['file_size']); ?>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <small><?php echo formatDateTime($sound['uploaded_at']); ?></small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm flex-wrap gap-1" role="group">
                                <a href="<?php echo ADMIN_URL; ?>/edit.php?id=<?php echo $sound['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm" title="編集（カテゴリーも変更可）">
                                    ✏️
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        onclick="confirmDelete(<?php echo $sound['id']; ?>, '<?php echo esc(addslashes($sound['title'])); ?>')">
                                    🗑️
                                </button>
                            </div>

                            <!-- 削除フォーム（非表示） -->
                            <form id="delete-form-<?php echo $sound['id']; ?>" 
                                  method="POST" action="delete.php" style="display: none;">
                                <input type="hidden" name="id" value="<?php echo $sound['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="text-muted small mt-3">
        📊 合計 <strong><?php echo count($sounds); ?></strong> 件の音源が登録されています。
    </p>
<?php endif; ?>

<!-- ===== 削除確認用JavaScrpit ===== -->
<form id="delete-form" method="GET" style="display:none;">
    <input type="hidden" name="action" value="delete_category">
    <input type="hidden" name="category_id" id="delete-category-id">
</form>

<script>
function confirmDelete(categoryId) {
    if (confirm('🗂️ このカテゴリーを削除してもよろしいですか？\n※ 関連する音源はカテゴリーなし（未分類）に移動します。')) {
        document.getElementById('delete-category-id').value = categoryId;
        document.getElementById('delete-form').submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
