<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT bc.*, c.objet_contrat, c.num_contrat, f.rs_four
    FROM bons_commande bc
    JOIN contrats c ON c.num_contrat = bc.num_contrat
    JOIN fournisseurs f ON f.id_four = c.id_four
    WHERE bc.num_bc = ?
");
$stmt->execute([$id]);
$bc = $stmt->fetch();
if (!$bc) { header('Location: list.php'); exit; }

$prestations = $pdo->prepare("SELECT * FROM prestations WHERE num_bc = ?");
$prestations->execute([$id]);
$prestations = $prestations->fetchAll();
$total = 0;
foreach ($prestations as $p) { $total += $p['quantite'] * $p['prix_unitaire']; }

$page_title = 'BC-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$page_crumb = 'Marchés / Bons de commande / Détails';

include __DIR__ . '/../includes/header.php';
?>

<div class="card-head">
    <h1>Bon de commande BC-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h1>
    <a href="list.php" class="btn outline small">← Retour</a>
</div>

<div class="card">
    <div class="form-grid">
        <div class="field"><label>Contrat</label><div><a href="<?= base_url('contrats/view.php?id=' . $bc['num_contrat']) ?>">CT-<?= str_pad($bc['num_contrat'], 4, '0', STR_PAD_LEFT) ?> — <?= e($bc['objet_contrat']) ?></a></div></div>
        <div class="field"><label>Fournisseur</label><div><?= e($bc['rs_four']) ?></div></div>
        <div class="field"><label>Date</label><div><?= fmt_date($bc['date_bc']) ?></div></div>
    </div>
</div>

<div class="card">
    <h2>Prestations</h2>
    <?php if (empty($prestations)): ?>
        <div class="empty-state">Aucune prestation enregistrée.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Désignation</th><th class="num">Quantité</th><th class="num">Prix unitaire</th><th class="num">Total</th></tr></thead>
        <tbody>
        <?php foreach ($prestations as $p): ?>
            <tr>
                <td><?= e($p['designation']) ?></td>
                <td class="num"><?= rtrim(rtrim(number_format($p['quantite'], 2, ',', ' '), '0'), ',') ?></td>
                <td class="num"><?= fmt_montant($p['prix_unitaire']) ?></td>
                <td class="num"><?= fmt_montant($p['quantite'] * $p['prix_unitaire']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" style="text-align:right; font-weight:600;">Total</td><td class="num" style="font-weight:600;"><?= fmt_montant($total) ?></td></tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
