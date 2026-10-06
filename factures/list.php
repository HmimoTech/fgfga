<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/workflow.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$page_title = 'Factures';
$page_crumb = 'Paiement / Factures';

$filtreStatut = $_GET['statut'] ?? '';
$sql = "
    SELECT f.*, c.rs_cl, fo.rs_four
    FROM factures f
    LEFT JOIN clients c ON c.num_cl = f.num_cl
    LEFT JOIN fournisseurs fo ON fo.id_four = f.id_four
";
$params = [];
if ($filtreStatut !== '') {
    $sql .= " WHERE f.statut = ?";
    $params[] = $filtreStatut;
}
$sql .= " ORDER BY f.date_fac DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$factures = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-head">
        <h2>Factures (<?= count($factures) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures'])): ?>
        <a href="add.php" class="btn flame">+ Nouvelle facture</a>
        <?php endif; ?>
    </div>

    <div style="margin-bottom:16px; display:flex; gap:8px;">
        <a href="list.php" class="btn small <?= $filtreStatut === '' ? '' : 'outline' ?>">Toutes</a>
        <a href="list.php?statut=en_attente" class="btn small <?= $filtreStatut === 'en_attente' ? '' : 'outline' ?>">En attente</a>
        <a href="list.php?statut=en_cours" class="btn small <?= $filtreStatut === 'en_cours' ? '' : 'outline' ?>">En cours</a>
        <a href="list.php?statut=reglee" class="btn small <?= $filtreStatut === 'reglee' ? '' : 'outline' ?>">Réglées</a>
    </div>

    <?php if (empty($factures)): ?>
        <div class="empty-state">Aucune facture trouvée.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>N°</th><th>Client</th><th>Fournisseur</th><th class="num">Montant</th><th>Statut</th><th>Date</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($factures as $f): ?>
            <tr>
                <td class="num">FAC-<?= str_pad($f['num_fac'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($f['rs_cl']) ?></td>
                <td><?= e($f['rs_four']) ?></td>
                <td class="num"><?= fmt_montant($f['montant_fac']) ?></td>
                <td><span class="badge <?= statut_fac_couleur($f['statut']) ?>"><?= e(statut_fac_label($f['statut'])) ?></span></td>
                <td><?= fmt_date($f['date_fac']) ?></td>
                <td>
                    <a href="view.php?id=<?= $f['num_fac'] ?>" class="btn outline small">Détails</a>
                    <?php if (in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures'])): ?>
                    <a href="edit.php?id=<?= $f['num_fac'] ?>" class="btn outline small">Modifier</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
