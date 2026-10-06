<?php
require_once __DIR__ . '/../includes/header_fournisseur.php';

$page_title = 'Mes contrats';
$page_crumb = 'Espace Fournisseur / Mes contrats';

$stmt = $pdo->prepare("SELECT * FROM contrats WHERE id_four = ? ORDER BY date_signature DESC");
$stmt->execute([$user['id_four']]);
$contrats = $stmt->fetchAll();
?>

<div class="card">
    <h2>Mes contrats (<?= count($contrats) ?>)</h2>
    <?php if (empty($contrats)): ?>
        <div class="empty-state">Aucun contrat enregistré pour votre entreprise.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th>Objet</th><th>Signature</th><th class="num">Montant HT</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($contrats as $c): ?>
            <tr>
                <td class="num">CT-<?= str_pad($c['num_contrat'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($c['objet_contrat']) ?></td>
                <td><?= fmt_date($c['date_signature']) ?></td>
                <td class="num"><?= fmt_montant($c['montant_ht']) ?></td>
                <td><a href="contrat_view.php?id=<?= $c['num_contrat'] ?>" class="btn outline small">Détails</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
