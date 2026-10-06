<?php
require_once __DIR__ . '/includes/auth.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'fournisseur' ? 'espace_fournisseur/dashboard.php' : 'dashboard/index.php'));
} else {
    header('Location: login.php');
}
exit;
