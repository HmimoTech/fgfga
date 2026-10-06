<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/**
 * Vérifie que l'utilisateur est connecté, sinon redirige vers le login.
 */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

/**
 * Vérifie que l'utilisateur a l'un des rôles autorisés.
 * @param array $roles ex: ['admin', 'ordonnateur']
 */
function require_role(array $roles) {
    require_login();
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        include __DIR__ . '/403.php';
        exit;
    }
}

function current_user() {
    return [
        'id'      => $_SESSION['user_id'] ?? null,
        'nom'     => $_SESSION['nom'] ?? '',
        'prenom'  => $_SESSION['prenom'] ?? '',
        'role'    => $_SESSION['role'] ?? '',
        'id_four' => $_SESSION['id_four'] ?? null,
    ];
}

function is_role($role) {
    return ($_SESSION['role'] ?? null) === $role;
}

/**
 * Construit un chemin absolu vers la racine du site.
 * Permet aux liens de fonctionner quel que soit le sous-dossier courant.
 */
function base_url($path = '') {
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // Remonte jusqu'à la racine du projet si on est dans un sous-dossier connu
    $subfolders = ['/clients', '/fournisseurs', '/contrats', '/bons_commande', '/attachements', '/factures', '/bons_paiement', '/dashboard', '/admin', '/espace_fournisseur'];
    foreach ($subfolders as $sub) {
        if (substr($root, -strlen($sub)) === $sub) {
            $root = substr($root, 0, -strlen($sub));
            break;
        }
    }
    return ($root === '' ? '' : $root) . '/' . ltrim($path, '/');
}

function log_activite($action, $cible = null) {
    global $pdo;
    $uid = $_SESSION['user_id'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO journal_activite (id_user, action, cible) VALUES (?, ?, ?)");
    $stmt->execute([$uid, $action, $cible]);
}

function role_label($role) {
    $labels = [
        'admin'                 => 'Administrateur',
        'ordonnateur'           => 'Ordonnateur',
        'tresorerie'            => 'Chargé de trésorerie',
        'comptable'             => 'Comptable',
        'gestionnaire_factures' => 'Gestionnaire de factures',
        'fournisseur'           => 'Fournisseur',
    ];
    return $labels[$role] ?? ucfirst($role);
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function fmt_montant($montant) {
    if ($montant === null) return '—';
    return number_format((float)$montant, 2, ',', ' ') . ' DA';
}

function fmt_date($date) {
    if (!$date) return '—';
    $d = DateTime::createFromFormat('Y-m-d', $date) ?: new DateTime($date);
    return $d->format('d/m/Y');
}
