<?php
require_once '../config.php';
require_once '../functions.php';

// セッション破棄
session_destroy();
$_SESSION = [];

setSuccessMessage('ログアウトしました。');
redirect(ADMIN_URL . '/');
