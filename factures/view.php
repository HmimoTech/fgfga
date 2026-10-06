<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/workflow.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);
$user = current_user();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT f.*, c.rs_cl, fo.rs_four
    FROM factures f
    LEFT JOIN clients c ON c.num_cl = f.num_cl
    LEFT JOIN fournisseurs fo ON fo.id_four = f.id_four
    WHERE f.num_fac = ?
");
$stmt->execute([$id]);
$f = $stmt->fetch();
if (!$f) { header('Location: list.php'); exit; }

// Lier à un bon de paiement existant (non affecté) ou en créer un nouveau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures']) && empty($f['num_bp'])) {
    if (isset($_POST['creer_bp'])) {
        $stmt = $pdo->prepare("INSERT INTO bons_paiement (date_creation_bp, num_cl) VALUES (?, ?)");
        $stmt->execute([date('Y-m-d'), $f['num_cl']]);
        $num_bp = $pdo->lastInsertId();
        $pdo->prepare("UPDATE factures SET num_bp = ? WHERE num_fac = ?")->execute([$num_bp, $id]);
        log_activite('Création bon de paiement depuis facture', "num_fac=$id");
        header('Location: view.php?id=' . $id);
        exit;
    } elseif (isset($_POST['lier_bp']) && !empty($_POST['num_bp'])) {
        $pdo->prepare("UPDATE factures SET num_bp = ? WHERE num_fac = ?")->execute([(int)$_POST['num_bp'], $id]);
        log_activite('Liaison bon de paiement', "num_fac=$id");
        header('Location: view.php?id=' . $id);
        exit;
    }
}

$bp = null;
if ($f['num_bp']) {
    $stmtBp = $pdo->prepare("SELECT * FROM bons_paiement WHERE num_bp = ?");
    $stmtBp->execute([$f['num_bp']]);
    $bp = $stmtBp->fetch();
}

$bpDisponibles = $pdo->query("SELECT num_bp, date_creation_bp, statut FROM bons_paiement WHERE num_bp NOT IN (SELECT COALESCE(num_bp,0) FROM factures WHERE num_bp IS NOT NULL AND num_fac != $id) ORDER BY num_bp DESC")->fetchAll();

$page_title = 'FAC-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$page_crumb = 'Paiement / Factures / Détails';

include __DIR__ . '/../includes/header.php';
?>

<div class="card-head">
    <h1>Facture FAC-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h1>
    <div>
        <?php if (in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures'])): ?>
        <a href="edit.php?id=<?= $id ?>" class="btn outline small">Modifier</a>
        <?php endif; ?>
        <a href="list.php" class="btn outline small">← Retour</a>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <div class="field"><label>Client</label><div><?= e($f['rs_cl']) ?></div></div>
        <div class="field"><label>Fournisseur</label><div><?= e($f['rs_four']) ?></div></div>
        <div class="field"><label>Date facture</label><div><?= fmt_date($f['date_fac']) ?></div></div>
        <div class="field"><label>Date de dépôt</label><div><?= fmt_date($f['ddp_fac']) ?></div></div>
        <div class="field"><label>Montant</label><div class="mono"><?= fmt_montant($f['montant_fac']) ?></div></div>
        <div class="field"><label>Statut</label><div><span class="badge <?= statut_fac_couleur($f['statut']) ?>"><?= e(statut_fac_label($f['statut'])) ?></span></div></div>
    </div>
</div>

<div class="card">
    <h2>Bon de paiement associé</h2>
    <?php if ($bp): ?>
        <p>
            <a href="<?= base_url('bons_paiement/view.php?id=' . $bp['num_bp']) ?>">BP-<?= str_pad($bp['num_bp'], 4, '0', STR_PAD_LEFT) ?></a>
            — <span class="badge <?= statut_bp_couleur($bp['statut']) ?>"><?= e(statut_bp_label($bp['statut'])) ?></span>
        </p>
    <?php else: ?>
        <div class="empty-state">Aucun bon de paiement lié pour le moment.</div>
        <?php if (in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures'])): ?>
        <form method="post" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-top:12px;">
            <button type="submit" name="creer_bp" value="1" class="btn flame small">+ Créer un nouveau bon de paiement</button>
        </form>
        <?php if (!empty($bpDisponibles)): ?>
        <form method="post" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-top:12px;">
            <div class="field" style="min-width:220px;">
                <label>Ou lier un bon de paiement existant</label>
                <select name="num_bp">
                    <?php foreach ($bpDisponibles as $b): ?>
                        <option value="<?= $b['num_bp'] ?>">BP-<?= str_pad($b['num_bp'], 4, '0', STR_PAD_LEFT) ?> (<?= e(statut_bp_label($b['statut'])) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="lier_bp" value="1" class="btn outline small">Lier</button>
        </form>
        <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
