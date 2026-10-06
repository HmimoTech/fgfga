<?php
require_once __DIR__ . '/auth.php';
require_login();
$user = current_user();
$current = basename($_SERVER['SCRIPT_NAME']);
$dir = basename(dirname($_SERVER['SCRIPT_NAME']));

function nav_active($dirCheck, $dir) {
    return $dir === $dirCheck ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' — ' : '' ?>SONATRACH · Suivi Factures &amp; Contrats</title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="org">SONATRACH</div>
            <div class="sub">Division PED · Suivi Factures &amp; Contrats</div>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= base_url('dashboard/index.php') ?>" class="<?= nav_active('dashboard', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                Tableau de bord
            </a>
            <div class="group-label">Tiers</div>
            <a href="<?= base_url('clients/list.php') ?>" class="<?= nav_active('clients', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Clients
            </a>
            <a href="<?= base_url('fournisseurs/list.php') ?>" class="<?= nav_active('fournisseurs', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="7" width="15" height="13"/><path d="M16 11h4l3 3v6h-7"/><circle cx="5.5" cy="20.5" r="1.5"/><circle cx="18.5" cy="20.5" r="1.5"/></svg>
                Fournisseurs
            </a>
            <div class="group-label">Marchés</div>
            <a href="<?= base_url('contrats/list.php') ?>" class="<?= nav_active('contrats', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                Contrats
            </a>
            <a href="<?= base_url('bons_commande/list.php') ?>" class="<?= nav_active('bons_commande', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 2h6l1 4H8z"/><path d="M4 6h16l-1.5 14a2 2 0 0 1-2 2H7.5a2 2 0 0 1-2-2z"/></svg>
                Bons de commande
            </a>
            <a href="<?= base_url('attachements/list.php') ?>" class="<?= nav_active('attachements', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                Attachements
            </a>
            <div class="group-label">Paiement</div>
            <a href="<?= base_url('factures/list.php') ?>" class="<?= nav_active('factures', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 2h13l3 3v17H4z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                Factures
            </a>
            <a href="<?= base_url('bons_paiement/list.php') ?>" class="<?= nav_active('bons_paiement', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                Bons de paiement
            </a>
            <?php if (is_role('admin')): ?>
            <div class="group-label">Administration</div>
            <a href="<?= base_url('admin/utilisateurs.php') ?>" class="<?= nav_active('admin', $dir) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                Utilisateurs
            </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-user">
            <div class="name"><?= e($user['prenom'] . ' ' . $user['nom']) ?></div>
            <div class="role"><?= e(role_label($user['role'])) ?></div>
            <form action="<?= base_url('logout.php') ?>" method="post">
                <button type="submit">Déconnexion</button>
            </form>
        </div>
    </aside>
    <div class="main">
        <div class="topbar">
            <div class="crumb"><?= isset($page_crumb) ? e($page_crumb) : '' ?></div>
        </div>
        <div class="content">
