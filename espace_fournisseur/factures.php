<?php
require_once __DIR__ . '/../includes/workflow.php';
require_once __DIR__ . '/../includes/header_fournisseur.php';

$page_title = 'Mes factures';
$page_crumb = 'Espace Fournisseur / Mes factures';

$stmt = $pdo->prepare("
    SELECT f.*, c.rs_cl
    FROM factures f
    LEFT JOIN clients c ON c.num_cl = f.num_cl
    WHERE f.id_four = ?
    ORDER BY f.date_fac DESC
");
$stmt->execute([$user['id_four']]);
$factures = $stmt->fetchAll();
?>

<div class="card">
    <h2>Mes factures (<?= count($factures) ?>)</h2>
    <?php if (empty($factures)): ?>
        <div class="empty-state">Aucune facture enregistrée pour votre entreprise.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th>Client</th><th class="num">Montant</th><th>Statut</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($factures as $f): ?>
            <tr>
                <td class="num">FAC-<?= str_pad($f['num_fac'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($f['rs_cl']) ?></td>
                <td class="num"><?= fmt_montant($f['montant_fac']) ?></td>
                <td><span class="badge <?= statut_fac_couleur($f['statut']) ?>"><?= e(statut_fac_label($f['statut'])) ?></span></td>
                <td><?= fmt_date($f['date_fac']) ?></td>
                <td><a href="facture_view.php?id=<?= $f['num_fac'] ?>" class="btn outline small">Détails</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
