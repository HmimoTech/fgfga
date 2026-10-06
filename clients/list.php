<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$page_title = 'Clients';
$page_crumb = 'Tiers / Clients';

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE rs_cl LIKE ? OR wilaya_cl LIKE ? ORDER BY rs_cl");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY rs_cl");
}
$clients = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-head">
        <h2>Clients (<?= count($clients) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur','gestionnaire_factures'])): ?>
        <a href="add.php" class="btn flame">+ Nouveau client</a>
        <?php endif; ?>
    </div>

    <form method="get" style="margin-bottom:16px;">
        <div class="field" style="max-width:320px;">
            <input type="text" name="q" placeholder="Rechercher par nom ou wilaya..." value="<?= e($search) ?>">
        </div>
    </form>

    <?php if (empty($clients)): ?>
        <div class="empty-state">Aucun client trouvé.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Raison sociale</th><th>Division</th><th>Wilaya</th><th>Commune</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($clients as $c): ?>
            <tr>
                <td><?= e($c['rs_cl']) ?></td>
                <td><?= e($c['div_cl']) ?></td>
                <td><?= e($c['wilaya_cl']) ?></td>
                <td><?= e($c['commune_cl']) ?></td>
                <td>
                    <a href="edit.php?id=<?= $c['num_cl'] ?>" class="btn outline small">Modifier</a>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="delete.php?id=<?= $c['num_cl'] ?>" class="btn danger small" onclick="return confirm('Supprimer ce client ?');">Suppr.</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
