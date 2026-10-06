<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$page_title = 'Attachements';
$page_crumb = 'Marchés / Attachements';

$attachements = $pdo->query("
    SELECT a.*, c.objet_contrat, c.num_contrat
    FROM attachements a
    JOIN contrats c ON c.num_contrat = a.num_contrat
    ORDER BY a.date_att DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-head">
        <h2>Attachements de travaux (<?= count($attachements) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur'])): ?>
        <a href="add.php" class="btn flame">+ Nouvel attachement</a>
        <?php endif; ?>
    </div>
    <?php if (empty($attachements)): ?>
        <div class="empty-state">Aucun attachement enregistré.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Contrat</th><th>Date</th><th>Description</th><th class="num">Montant</th></tr></thead>
        <tbody>
        <?php foreach ($attachements as $a): ?>
            <tr>
                <td><a href="<?= base_url('contrats/view.php?id=' . $a['num_contrat']) ?>">CT-<?= str_pad($a['num_contrat'], 4, '0', STR_PAD_LEFT) ?> — <?= e($a['objet_contrat']) ?></a></td>
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
