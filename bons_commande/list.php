<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$page_title = 'Bons de commande';
$page_crumb = 'Marchés / Bons de commande';

$bons = $pdo->query("
    SELECT bc.*, c.objet_contrat, f.rs_four
    FROM bons_commande bc
    JOIN contrats c ON c.num_contrat = bc.num_contrat
    JOIN fournisseurs f ON f.id_four = c.id_four
    ORDER BY bc.date_bc DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-head">
        <h2>Bons de commande (<?= count($bons) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur'])): ?>
        <a href="add.php" class="btn flame">+ Nouveau bon de commande</a>
        <?php endif; ?>
    </div>
    <?php if (empty($bons)): ?>
        <div class="empty-state">Aucun bon de commande enregistré.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th>Contrat</th><th>Fournisseur</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($bons as $b): ?>
            <tr>
                <td class="num">BC-<?= str_pad($b['num_bc'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($b['objet_contrat']) ?></td>
                <td><?= e($b['rs_four']) ?></td>
                <td><?= fmt_date($b['date_bc']) ?></td>
                <td><a href="view.php?id=<?= $b['num_bc'] ?>" class="btn outline small">Détails</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
