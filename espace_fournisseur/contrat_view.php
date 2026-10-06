<?php
require_once __DIR__ . '/../includes/header_fournisseur.php';

$id = (int)($_GET['id'] ?? 0);

// Vérification stricte : ce contrat doit appartenir au fournisseur connecté
$stmt = $pdo->prepare("SELECT * FROM contrats WHERE num_contrat = ? AND id_four = ?");
$stmt->execute([$id, $user['id_four']]);
$c = $stmt->fetch();
if (!$c) { header('Location: contrats.php'); exit; }

$page_title = 'CT-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$page_crumb = 'Espace Fournisseur / Mes contrats / Détails';

$bons = $pdo->prepare("SELECT * FROM bons_commande WHERE num_contrat = ? ORDER BY date_bc DESC");
$bons->execute([$id]);
$bons = $bons->fetchAll();

$attachements = $pdo->prepare("SELECT * FROM attachements WHERE num_contrat = ? ORDER BY date_att DESC");
$attachements->execute([$id]);
$attachements = $attachements->fetchAll();
?>

<div class="card-head">
    <h1>Contrat CT-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h1>
    <a href="contrats.php" class="btn outline small">← Retour</a>
</div>

<div class="card">
    <div class="form-grid">
        <div class="field full"><label>Objet</label><div><?= e($c['objet_contrat']) ?></div></div>
        <div class="field"><label>Date de signature</label><div><?= fmt_date($c['date_signature']) ?></div></div>
        <div class="field"><label>Montant HT</label><div class="mono"><?= fmt_montant($c['montant_ht']) ?></div></div>
    </div>
</div>

<div class="card">
    <h2>Bons de commande (<?= count($bons) ?>)</h2>
    <?php if (empty($bons)): ?>
        <div class="empty-state">Aucun bon de commande pour ce contrat.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($bons as $b): ?>
            <tr><td class="num">BC-<?= str_pad($b['num_bc'], 4, '0', STR_PAD_LEFT) ?></td><td><?= fmt_date($b['date_bc']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Attachements de travaux (<?= count($attachements) ?>)</h2>
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
