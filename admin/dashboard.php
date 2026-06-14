<?php
require_once '../config.php';
require_once '../functions.php';

// ログインチェック
requireLogin();

$page_title = '音源一覧';

// DB接続
global $db;

// ===== 大カテゴリ新規追加処理 =====
if (($_POST['action'] ?? null) === 'add_parent_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setErrorMessage('セキュリティトークンが無効です。');
        redirect(ADMIN_URL . '/dashboard.php');
    }
    
    $category_name = trim($_POST['category_name'] ?? '');
    $category_order = (int)($_POST['category_order'] ?? 0);
    
    if (!empty($category_name)) {
        $sql = "INSERT INTO categories (name, display_order) VALUES (?, ?)";
        if ($db->execute($sql, [$category_name, $category_order])) {
            // リダイレクト（キャッシュ対策）
            header('Location: dashboard.php?message=added');
            exit;
        } else {
            setErrorMessage('大カテゴリーの追加に失敗しました: ' . $db->getError());
            redirect(ADMIN_URL . '/dashboard.php');
        }
    } else {
        setErrorMessage('大カテゴリー名を入力してください。');
        redirect(ADMIN_URL . '/dashboard.php');
    }
}

// ===== 大カテゴリ削除処理 =====
if (($_GET['action'] ?? null) === 'delete_parent_category') {
    $category_id = (int)$_GET['category_id'];
    
    // 関連する小カテゴリと音源をクリーンアップ
    $sql_update = "UPDATE sounds SET category_id = NULL 
                   WHERE category_id IN (SELECT id FROM sub_categories WHERE parent_id = ?)";
    $db->execute($sql_update, [$category_id]);
    
    // 小カテゴリを削除
    $sql_delete_sub = "DELETE FROM sub_categories WHERE parent_id = ?";
    $db->execute($sql_delete_sub, [$category_id]);
    
    // 大カテゴリを削除
    $sql_delete = "DELETE FROM categories WHERE id = ?";
    $db->execute($sql_delete, [$category_id]);
    
    // リダイレクト
    header('Location: dashboard.php?message=parent_deleted');
    exit;
}

// ===== 小カテゴリ新規追加処理 =====
if (($_POST['action'] ?? null) === 'add_sub_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setErrorMessage('セキュリティトークンが無効です。');
        redirect(ADMIN_URL . '/dashboard.php');
    }
    
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    $sub_category_name = trim($_POST['sub_category_name'] ?? '');
    $sub_category_order = (int)($_POST['sub_category_order'] ?? 0);
    
    if ($parent_id > 0 && !empty($sub_category_name)) {
        $sql = "INSERT INTO sub_categories (parent_id, name, display_order) VALUES (?, ?, ?)";
        if ($db->execute($sql, [$parent_id, $sub_category_name, $sub_category_order])) {
            header('Location: dashboard.php?message=sub_added');
            exit;
        } else {
            setErrorMessage('小カテゴリーの追加に失敗しました: ' . $db->getError());
            redirect(ADMIN_URL . '/dashboard.php');
        }
    } else {
        setErrorMessage('大カテゴリーと小カテゴリー名を入力してください。');
        redirect(ADMIN_URL . '/dashboard.php');
    }
}

// ===== 小カテゴリ削除処理 =====
if (($_GET['action'] ?? null) === 'delete_sub_category') {
    $sub_category_id = (int)$_GET['sub_id'];
    
    // 関連する音源のcategory_idをNULLに更新
    $sql_update = "UPDATE sounds SET category_id = NULL WHERE category_id = ?";
    $db->execute($sql_update, [$sub_category_id]);
    
    // 小カテゴリを削除
    $sql_delete = "DELETE FROM sub_categories WHERE id = ?";
    $db->execute($sql_delete, [$sub_category_id]);
    
    header('Location: dashboard.php?message=sub_deleted');
    exit;
}

// ===== 従来のカテゴリー処理（互換性保持）
if (($_POST['action'] ?? null) === 'add_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: dashboard.php?action=add_parent_category');
    exit;
}

// ===== おすすめレコード新規追加処理 =====
if (($_POST['action'] ?? null) === 'add_featured' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $featured_sound_id = (int)($_POST['featured_sound_id'] ?? 0);
    
    if ($featured_sound_id > 0) {
        // 既に登録されていないか確認
        $existing = $db->selectOne('SELECT id FROM featured_records WHERE sound_id = ?', [$featured_sound_id]);
        
        if (!$existing) {
            // 最大4件までの制限をチェック
            $count_result = $db->selectOne('SELECT COUNT(*) as cnt FROM featured_records', []);
            $current_count = isset($count_result['cnt']) ? (int)$count_result['cnt'] : 0;
            
            if ($current_count >= 4) {
                // 最古のものを削除
                $oldest = $db->selectOne('SELECT id FROM featured_records ORDER BY featured_at ASC LIMIT 1', []);
                if ($oldest) {
                    $db->execute('DELETE FROM featured_records WHERE id = ?', [$oldest['id']]);
                }
            }
            
            // 新規追加
            $max_result = $db->selectOne('SELECT MAX(display_order) as max_order FROM featured_records', []);
            $display_order = (isset($max_result['max_order']) && $max_result['max_order'] !== null) ? ((int)$max_result['max_order'] + 1) : 0;
            
            $result = $db->execute('INSERT INTO featured_records (sound_id, display_order) VALUES (?, ?)', 
                        [$featured_sound_id, $display_order]);
            
           
            
            // リダイレクト
            header('Location: dashboard.php?message=featured_added');
            exit;
        } else {
            // 既に登録されている場合
            header('Location: dashboard.php?message=featured_exists');
            exit;
        }
    }
}

// ===== おすすめレコード削除処理 =====
if (($_GET['action'] ?? null) === 'delete_featured') {
    $featured_id = (int)$_GET['featured_id'];
    
    if ($featured_id > 0) {
        $db->execute('DELETE FROM featured_records WHERE id = ?', [$featured_id]);
        
        // リダイレクト
        header('Location: dashboard.php?message=featured_deleted');
        exit;
    }
}

// 検索キーワード取得
$search = $_GET['search'] ?? '';
$search = trim($search);

// SQLクエリ構築
$sql = 'SELECT s.*, 
               sc.name as sub_category_name, sc.parent_id,
               c.name as parent_category_name
        FROM sounds s 
        LEFT JOIN sub_categories sc ON s.category_id = sc.id
        LEFT JOIN categories c ON sc.parent_id = c.id';
$params = [];

if (!empty($search)) {
    $sql .= ' WHERE s.title LIKE ?';
    $params = ['%' . $search . '%'];
}

$sql .= ' ORDER BY s.uploaded_at DESC LIMIT 100';

// データ取得
$sounds = $db->select($sql, $params);

// おすすめレコード一覧取得（管理用）
$featured_sounds = $db->select(
    "SELECT fr.id, fr.display_order, s.id as sound_id, s.title, s.filename
     FROM featured_records fr
     JOIN sounds s ON fr.sound_id = s.id
     ORDER BY fr.display_order ASC"
);

// 大カテゴリー一覧取得
$sql_parent_categories = "SELECT c.id, c.name, c.display_order, COUNT(sc.id) as sub_count
                          FROM categories c 
                          LEFT JOIN sub_categories sc ON c.id = sc.parent_id
                          GROUP BY c.id, c.name, c.display_order
                          ORDER BY c.display_order ASC";
$parent_categories = $db->select($sql_parent_categories);

// 小カテゴリー一覧取得
$sql_sub_categories = "SELECT sc.id, sc.parent_id, sc.name, sc.display_order, 
                              COUNT(s.id) as sound_count,
                              c.name as parent_name
                       FROM sub_categories sc
                       LEFT JOIN sounds s ON sc.id = s.category_id
                       LEFT JOIN categories c ON sc.parent_id = c.id
                       GROUP BY sc.id, sc.parent_id, sc.name, sc.display_order, c.name
                       ORDER BY sc.parent_id ASC, sc.display_order ASC";
$sub_categories = $db->select($sql_sub_categories);

// messages 
$success_message = getSessionMessage('success');
$error_message = getSessionMessage('error');

// メッセージ確認
if (($_GET['message'] ?? null) === 'parent_deleted') {
    $success_message = '✅ 大カテゴリーを削除しました。関連する小カテゴリーと音源は削除またはカテゴリーなしに移動されました。';
}

if (($_GET['message'] ?? null) === 'deleted' || ($_GET['message'] ?? null) === 'added') {
    $success_message = $success_message ?? '✅ 大カテゴリーを追加しました。';
}

if (($_GET['message'] ?? null) === 'sub_deleted') {
    $success_message = '✅ 小カテゴリーを削除しました。関連する音源はカテゴリーなしに移動されました。';
}

if (($_GET['message'] ?? null) === 'sub_added') {
    $success_message = '✅ 新しい小カテゴリーを追加しました。';
}

// おすすめレコード追加メッセージ
if (($_GET['message'] ?? null) === 'featured_added') {
    $success_message = '✅ おすすめレコードに追加しました。';
}

// おすすめレコード削除メッセージ
if (($_GET['message'] ?? null) === 'featured_deleted') {
    $success_message = '✅ おすすめレコードから削除しました。';
}

// おすすめレコード重複エラー
if (($_GET['message'] ?? null) === 'featured_exists') {
    $error_message = '⚠️ このレコードはすでにおすすめに登録されています。';
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
            <h5 style="margin-bottom: 15px;">新規大カテゴリー追加</h5>
            <form method="POST" class="row g-3" style="max-width: 500px;">
                <input type="hidden" name="action" value="add_parent_category">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="col-12">
                    <label for="category_name" class="form-label">大カテゴリー名</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" 
                           placeholder="例：効果音、環境音..." required>
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

        <!-- 大カテゴリー一覧 -->
        <h5 style="margin: 15px 0;">大カテゴリー一覧</h5>
        <?php if (empty($parent_categories)): ?>
            <p class="text-muted">大カテゴリーがまだ登録されていません。</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>大カテゴリー名</th>
                            <th>表示順序</th>
                            <th>小カテゴリー数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parent_categories as $pcat): ?>
                        <tr>
                            <td><small>#<?php echo $pcat['id']; ?></small></td>
                            <td><?php echo esc($pcat['name']); ?></td>
                            <td><?php echo $pcat['display_order']; ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $pcat['sub_count']; ?></span>
                            </td>
                            <td>
                                <a href="javascript:void(0);" onclick="confirmDeleteParent(<?php echo $pcat['id']; ?>)" 
                                   class="btn btn-sm btn-danger" title="削除">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <hr>

        <!-- 小カテゴリー一覧 -->
        <h5 style="margin: 15px 0;">小カテゴリー一覧</h5>
        
        <!-- 小カテゴリー追加フォーム -->
        <div class="mb-4">
            <h6 style="margin-bottom: 10px;">新規小カテゴリー追加</h6>
            <form method="POST" class="row g-3" style="max-width: 600px;">
                <input type="hidden" name="action" value="add_sub_category">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="col-md-6">
                    <label for="parent_id" class="form-label">大カテゴリー</label>
                    <select class="form-control" id="parent_id" name="parent_id" required>
                        <option value="">-- 大カテゴリーを選択 --</option>
                        <?php foreach ($parent_categories as $pcat): ?>
                            <option value="<?php echo $pcat['id']; ?>">
                                <?php echo esc($pcat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="sub_category_name" class="form-label">小カテゴリー名</label>
                    <input type="text" class="form-control" id="sub_category_name" name="sub_category_name" 
                           placeholder="例：物音、SE..." required>
                </div>
                <div class="col-md-4">
                    <label for="sub_category_order" class="form-label">表示順序</label>
                    <input type="number" class="form-control" id="sub_category_order" name="sub_category_order" 
                           value="0" min="0">
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <button type="submit" class="btn btn-info w-100">➕ 小カテゴリーを追加</button>
                </div>
            </form>
        </div>

        <hr>

        <?php if (empty($sub_categories)): ?>
            <p class="text-muted">小カテゴリーがまだ登録されていません。</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>大カテゴリー</th>
                            <th>小カテゴリー名</th>
                            <th>表示順序</th>
                            <th>音源数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sub_categories as $scat): ?>
                        <tr>
                            <td><small>#<?php echo $scat['id']; ?></small></td>
                            <td><?php echo esc($scat['parent_name'] ?? '（削除済み）'); ?></td>
                            <td><?php echo esc($scat['name']); ?></td>
                            <td><?php echo $scat['display_order']; ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $scat['sound_count']; ?></span>
                            </td>
                            <td>
                                <a href="javascript:void(0);" onclick="confirmDeleteSub(<?php echo $scat['id']; ?>)" 
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

<!-- ===== おすすめレコード管理セクション ===== -->
<div class="card mb-5" style="border-left: 5px solid #764ba2;">
    <div class="card-header bg-light">
        <h4 class="mb-0">⭐ おすすめレコード管理</h4>
    </div>
    <div class="card-body">
        <!-- 新規追加フォーム -->
        <div class="mb-4">
            <h5 style="margin-bottom: 15px;">新規おすすめレコード追加</h5>
            <form method="POST" id="add-featured-form" class="row g-3" style="max-width: 500px;">
                <input type="hidden" name="action" value="add_featured">
                <div class="col-12">
                    <label for="featured_sound_id" class="form-label">音源ID / 音源を選択</label>
                    <select class="form-control" id="featured_sound_id" name="featured_sound_id" required>
                        <option value="">-- 公開中の音源から選択 --</option>
                        <?php 
                        $public_sounds = $db->select("SELECT id, title FROM sounds WHERE is_public = 1 ORDER BY title ASC");
                        foreach ($public_sounds as $s):
                        ?>
                            <option value="<?php echo $s['id']; ?>">
                                [ID: <?php echo $s['id']; ?>] <?php echo esc($s['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">➕ 追加</button>
                </div>
            </form>
        </div>

        <hr>

        <!-- おすすめレコード一覧 -->
        <h5 style="margin: 15px 0;">登録済みおすすめレコード（最大4件）</h5>
        <?php if (empty($featured_sounds)): ?>
            <p class="text-muted">おすすめレコードが登録されていません。</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">順序</th>
                            <th>ID</th>
                            <th>タイトル</th>
                            <th style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($featured_sounds as $fs): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary" style="font-size: 0.9em;">
                                    <?php echo $fs['display_order'] + 1; ?>
                                </span>
                            </td>
                            <td><small>#<?php echo $fs['sound_id']; ?></small></td>
                            <td><?php echo esc($fs['title']); ?></td>
                            <td>
                                <a href="javascript:void(0);" 
                                   onclick="confirmDeleteFeatured(<?php echo $fs['id']; ?>, '<?php echo esc(addslashes($fs['title'])); ?>')" 
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
                            <span class="badge bg-info text-dark">📁 <?php echo esc($sound['parent_category_name'] ?? '（未分類）') . ' > ' . esc($sound['sub_category_name'] ?? '（なし）'); ?></span>
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
<form id="delete-parent-form" method="GET" style="display:none;">
    <input type="hidden" name="action" value="delete_parent_category">
    <input type="hidden" name="category_id" id="delete-parent-id">
</form>

<form id="delete-sub-form" method="GET" style="display:none;">
    <input type="hidden" name="action" value="delete_sub_category">
    <input type="hidden" name="sub_id" id="delete-sub-id">
</form>

<script>
function confirmDeleteParent(categoryId) {
    if (confirm('🗂️ この大カテゴリーを削除してもよろしいですか？\n※ 配下の小カテゴリーと関連する音源は削除されます。')) {
        document.getElementById('delete-parent-id').value = categoryId;
        document.getElementById('delete-parent-form').submit();
    }
}

function confirmDeleteSub(subCategoryId) {
    if (confirm('📂 この小カテゴリーを削除してもよろしいですか？\n※ 関連する音源はカテゴリーなしに移動します。')) {
        document.getElementById('delete-sub-id').value = subCategoryId;
        document.getElementById('delete-sub-form').submit();
    }
}

function confirmDeleteFeatured(featuredId, title) {
    if (confirm('⭐ 「' + title + '」をおすすめレコードから削除してもよろしいですか？')) {
        window.location.href = 'dashboard.php?action=delete_featured&featured_id=' + featuredId;
    }
}
</script>

<?php require_once 'footer.php'; ?>
