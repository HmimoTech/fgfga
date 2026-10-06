<?php
require_once __DIR__ . '/includes/auth.php';
if (!empty($_SESSION['user_id'])) {
    log_activite('Déconnexion');
}
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
