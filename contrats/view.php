<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, f.rs_four, f.id_four FROM contrats c JOIN fournisseurs f ON f.id_four = c.id_four WHERE c.num_contrat = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header('Location: list.php'); exit; }

$page_title = 'CT-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$page_crumb = 'Marchés / Contrats / Détails';

$bons = $pdo->prepare("SELECT * FROM bons_commande WHERE num_contrat = ? ORDER BY date_bc DESC");
$bons->execute([$id]);
$bons = $bons->fetchAll();

$attachements = $pdo->prepare("SELECT * FROM attachements WHERE num_contrat = ? ORDER BY date_att DESC");
$attachements->execute([$id]);
$attachements = $attachements->fetchAll();

$totalAttache = array_sum(array_column($attachements, 'montant_att'));

include __DIR__ . '/../includes/header.php';
?>

<div class="card-head">
    <h1>Contrat CT-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h1>
    <a href="list.php" class="btn outline small">← Retour</a>
</div>

<div class="card">
    <div class="form-grid">
        <div class="field full"><label>Objet</label><div><?= e($c['objet_contrat']) ?></div></div>
        <div class="field"><label>Fournisseur</label><div><a href="<?= base_url('fournisseurs/view.php?id=' . $c['id_four']) ?>"><?= e($c['rs_four']) ?></a></div></div>
        <div class="field"><label>Date de signature</label><div><?= fmt_date($c['date_signature']) ?></div></div>
        <div class="field"><label>Montant HT</label><div class="mono"><?= fmt_montant($c['montant_ht']) ?></div></div>
        <div class="field"><label>Avancement (attachements)</label><div class="mono"><?= fmt_montant($totalAttache) ?></div></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Bons de commande (<?= count($bons) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur'])): ?>
        <a href="<?= base_url('bons_commande/add.php?contrat=' . $id) ?>" class="btn outline small">+ Nouveau bon de commande</a>
        <?php endif; ?>
    </div>
    <?php if (empty($bons)): ?>
        <div class="empty-state">Aucun bon de commande pour ce contrat.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($bons as $b): ?>
            <tr>
                <td class="num">BC-<?= str_pad($b['num_bc'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= fmt_date($b['date_bc']) ?></td>
                <td><a href="<?= base_url('bons_commande/view.php?id=' . $b['num_bc']) ?>" class="btn outline small">Voir</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <h2>Attachements de travaux (<?= count($attachements) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur'])): ?>
        <a href="<?= base_url('attachements/add.php?contrat=' . $id) ?>" class="btn outline small">+ Nouvel attachement</a>
        <?php endif; ?>
    </div>
    <?php if (empty($attachements)): ?>
        <div class="empty-state">Aucun attachement enregistré pour ce contrat.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Date</th><th>Description</th><th class="num">Montant</th></tr></thead>
        <tbody>
        <?php foreach ($attachements as $a): ?>
            <tr>
                <td><?= fmt_date($a['date_att']) ?></td>
                <td><?= e($a['description']) ?></td>
                <td class="num"><?= fmt_montant($a['montant_att']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
