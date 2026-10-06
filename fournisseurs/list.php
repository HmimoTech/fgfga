<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$page_title = 'Fournisseurs';
$page_crumb = 'Tiers / Fournisseurs';

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE rs_four LIKE ? OR nis_four LIKE ? ORDER BY rs_four");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM fournisseurs ORDER BY rs_four");
}
$fournisseurs = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-head">
        <h2>Fournisseurs (<?= count($fournisseurs) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures'])): ?>
        <a href="add.php" class="btn flame">+ Nouveau fournisseur</a>
        <?php endif; ?>
    </div>

    <form method="get" style="margin-bottom:16px;">
        <div class="field" style="max-width:320px;">
            <input type="text" name="q" placeholder="Rechercher par nom ou NIS..." value="<?= e($search) ?>">
        </div>
    </form>

    <?php if (empty($fournisseurs)): ?>
        <div class="empty-state">Aucun fournisseur trouvé.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Raison sociale</th><th>Contact</th><th>Téléphone</th><th>NIS</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($fournisseurs as $f): ?>
            <tr>
                <td><?= e($f['rs_four']) ?></td>
                <td><?= e(trim($f['nom_four'] . ' ' . $f['pre_four'])) ?></td>
                <td class="num"><?= e($f['num_tel_four']) ?></td>
                <td class="num"><?= e($f['nis_four']) ?></td>
                <td>
                    <a href="view.php?id=<?= $f['id_four'] ?>" class="btn outline small">Détails</a>
                    <?php if (in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures'])): ?>
                    <a href="edit.php?id=<?= $f['id_four'] ?>" class="btn outline small">Modifier</a>
                    <?php endif; ?>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="delete.php?id=<?= $f['id_four'] ?>" class="btn danger small" onclick="return confirm('Supprimer ce fournisseur ?');">Suppr.</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
