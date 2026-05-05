<?php
require_once '../config.php';
require_once '../functions.php';

requireLogin();

$page_title = 'お知らせ管理';
global $db;

// messages
$success_message = getSessionMessage('success');
$error_message = getSessionMessage('error');

// 一覧取得
$news = $db->select(
    "SELECT *
     FROM NewsRelease
     ORDER BY publish_start DESC, id DESC
     LIMIT 200"
);

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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📰 お知らせ管理</h2>
    <a href="<?php echo ADMIN_URL; ?>/news-create.php" class="btn btn-primary">
        ➕ 新規作成
    </a>
</div>

<?php if (empty($news)): ?>
    <div class="no-data">
        <p>📭 お知らせがまだ登録されていません。</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>タイトル</th>
                    <th class="d-none d-md-table-cell">重要</th>
                    <th class="d-none d-lg-table-cell">公開開始</th>
                    <th class="d-none d-lg-table-cell">公開終了</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($news as $row): ?>
                    <?php
                    $now = time();
                    $start_ts = strtotime($row['publish_start']);
                    $end_ts = $row['publish_end'] ? strtotime($row['publish_end']) : null;
                    $is_active = ($start_ts !== false && $start_ts <= $now) && ($end_ts === null || $end_ts === false || $end_ts >= $now);
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <strong><?php echo esc($row['title']); ?></strong>
                                <?php if ((int)$row['is_important'] === 1): ?>
                                    <span class="badge bg-danger">重要</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted d-block">
                                #<?php echo (int)$row['id']; ?>
                            </small>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php if ((int)$row['is_important'] === 1): ?>
                                <span class="badge bg-danger">重要</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">通常</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <small><?php echo esc($row['publish_start']); ?></small>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <small><?php echo $row['publish_end'] ? esc($row['publish_end']) : '（終了なし）'; ?></small>
                        </td>
                        <td>
                            <?php if ($is_active): ?>
                                <span class="badge bg-success">公開中</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">非公開</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm flex-wrap gap-1" role="group">
                                <a href="<?php echo ADMIN_URL; ?>/news-edit.php?id=<?php echo (int)$row['id']; ?>"
                                   class="btn btn-outline-primary btn-sm" title="編集">
                                    ✏️
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="confirmDelete(<?php echo (int)$row['id']; ?>, '<?php echo esc(addslashes($row['title'])); ?>')">
                                    🗑️
                                </button>
                            </div>

                            <form id="delete-form-<?php echo (int)$row['id']; ?>"
                                  method="POST" action="news-delete.php" style="display:none;">
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

