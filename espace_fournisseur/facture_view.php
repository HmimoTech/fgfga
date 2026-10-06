<?php
require_once __DIR__ . '/../includes/workflow.php';
require_once __DIR__ . '/../includes/header_fournisseur.php';

$id = (int)($_GET['id'] ?? 0);

// Vérification stricte : cette facture doit appartenir au fournisseur connecté
$stmt = $pdo->prepare("
    SELECT f.*, c.rs_cl
    FROM factures f
    LEFT JOIN clients c ON c.num_cl = f.num_cl
    WHERE f.num_fac = ? AND f.id_four = ?
");
$stmt->execute([$id, $user['id_four']]);
$f = $stmt->fetch();
if (!$f) { header('Location: factures.php'); exit; }

$bp = null;
if ($f['num_bp']) {
    $stmtBp = $pdo->prepare("SELECT * FROM bons_paiement WHERE num_bp = ?");
    $stmtBp->execute([$f['num_bp']]);
    $bp = $stmtBp->fetch();
}

$page_title = 'FAC-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$page_crumb = 'Espace Fournisseur / Mes factures / Détails';
?>

<div class="card-head">
    <h1>Facture FAC-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h1>
    <a href="factures.php" class="btn outline small">← Retour</a>
</div>

<div class="card">
    <div class="form-grid">
        <div class="field"><label>Client</label><div><?= e($f['rs_cl']) ?></div></div>
        <div class="field"><label>Date facture</label><div><?= fmt_date($f['date_fac']) ?></div></div>
        <div class="field"><label>Montant</label><div class="mono"><?= fmt_montant($f['montant_fac']) ?></div></div>
        <div class="field"><label>Statut</label><div><span class="badge <?= statut_fac_couleur($f['statut']) ?>"><?= e(statut_fac_label($f['statut'])) ?></span></div></div>
    </div>
</div>

<div class="card">
    <h2>Avancement du paiement</h2>
    <?php if (!$bp): ?>
        <div class="empty-state">Le traitement du paiement n'a pas encore démarré.</div>
    <?php else: ?>
    <p>Statut actuel : <span class="badge <?= statut_bp_couleur($bp['statut']) ?>"><?= e(statut_bp_label($bp['statut'])) ?></span></p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
