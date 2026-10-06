<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$page_title = 'Contrats';
$page_crumb = 'Marchés / Contrats';

$search = trim($_GET['q'] ?? '');
$sql = "SELECT c.*, f.rs_four FROM contrats c JOIN fournisseurs f ON f.id_four = c.id_four";
if ($search !== '') {
    $sql .= " WHERE c.objet_contrat LIKE ? OR f.rs_four LIKE ?";
    $stmt = $pdo->prepare($sql . " ORDER BY c.date_signature DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query($sql . " ORDER BY c.date_signature DESC");
}
$contrats = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-head">
        <h2>Contrats (<?= count($contrats) ?>)</h2>
        <?php if (in_array($user['role'], ['admin','ordonnateur'])): ?>
        <a href="add.php" class="btn flame">+ Nouveau contrat</a>
        <?php endif; ?>
    </div>

    <form method="get" style="margin-bottom:16px;">
        <div class="field" style="max-width:320px;">
            <input type="text" name="q" placeholder="Rechercher par objet ou fournisseur..." value="<?= e($search) ?>">
        </div>
    </form>

    <?php if (empty($contrats)): ?>
        <div class="empty-state">Aucun contrat trouvé.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>N°</th><th>Objet</th><th>Fournisseur</th><th>Signature</th><th class="num">Montant HT</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($contrats as $c): ?>
            <tr>
                <td class="num">CT-<?= str_pad($c['num_contrat'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($c['objet_contrat']) ?></td>
                <td><?= e($c['rs_four']) ?></td>
                <td><?= fmt_date($c['date_signature']) ?></td>
                <td class="num"><?= fmt_montant($c['montant_ht']) ?></td>
                <td>
                    <a href="view.php?id=<?= $c['num_contrat'] ?>" class="btn outline small">Détails</a>
                    <?php if (in_array($user['role'], ['admin','ordonnateur'])): ?>
                    <a href="edit.php?id=<?= $c['num_contrat'] ?>" class="btn outline small">Modifier</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
