<?php
require_once __DIR__ . '/auth.php';
require_role(['fournisseur']);
$user = current_user();
$current = basename($_SERVER['SCRIPT_NAME']);

$stmtFour = $pdo->prepare("SELECT rs_four FROM fournisseurs WHERE id_four = ?");
$stmtFour->execute([$user['id_four']]);
$monFournisseur = $stmtFour->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' — ' : '' ?>Espace Fournisseur — SONATRACH</title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="org">SONATRACH</div>
            <div class="sub">Espace Fournisseur</div>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= base_url('espace_fournisseur/dashboard.php') ?>" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                Tableau de bord
            </a>
            <a href="<?= base_url('espace_fournisseur/contrats.php') ?>" class="<?= in_array($current, ['contrats.php','contrat_view.php']) ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                Mes contrats
            </a>
            <a href="<?= base_url('espace_fournisseur/factures.php') ?>" class="<?= in_array($current, ['factures.php','facture_view.php']) ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 2h13l3 3v17H4z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                Mes factures
            </a>
        </nav>
        <div class="sidebar-user">
            <div class="name"><?= e($user['prenom'] . ' ' . $user['nom']) ?></div>
            <div class="role"><?= e($monFournisseur ?: 'Fournisseur') ?></div>
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
