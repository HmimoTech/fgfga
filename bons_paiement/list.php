<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/workflow.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$page_title = 'Bons de paiement';
$page_crumb = 'Paiement / Bons de paiement';

$bons = $pdo->query("
    SELECT bp.*, c.rs_cl
    FROM bons_paiement bp
    LEFT JOIN clients c ON c.num_cl = bp.num_cl
    ORDER BY bp.date_creation_bp DESC, bp.num_bp DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-head">
        <h2>Bons de paiement (<?= count($bons) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur'])): ?>
        <a href="add.php" class="btn flame">+ Nouveau bon de paiement</a>
        <?php endif; ?>
    </div>
    <?php if (empty($bons)): ?>
        <div class="empty-state">Aucun bon de paiement enregistré.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th>Client</th><th>Statut</th><th>Créé le</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($bons as $b):
            $mesActions = actions_possibles_bp($b['statut'], $user['role']);
        ?>
            <tr>
                <td class="num">BP-<?= str_pad($b['num_bp'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($b['rs_cl'] ?? '—') ?></td>
                <td><span class="badge <?= statut_bp_couleur($b['statut']) ?>"><?= e(statut_bp_label($b['statut'])) ?></span></td>
                <td><?= fmt_date($b['date_creation_bp']) ?></td>
                <td>
                    <a href="view.php?id=<?= $b['num_bp'] ?>" class="btn outline small"><?= empty($mesActions) ? 'Détails' : 'Traiter' ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
