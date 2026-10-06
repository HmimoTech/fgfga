<?php
require_once __DIR__ . '/../includes/workflow.php';
require_once __DIR__ . '/../includes/header_fournisseur.php';

$page_title = 'Tableau de bord';
$page_crumb = 'Espace Fournisseur / Tableau de bord';

$idFour = $user['id_four'];

$nbContrats = $pdo->prepare("SELECT COUNT(*) FROM contrats WHERE id_four = ?");
$nbContrats->execute([$idFour]);
$nbContrats = $nbContrats->fetchColumn();

$stmtF = $pdo->prepare("SELECT * FROM factures WHERE id_four = ?");
$stmtF->execute([$idFour]);
$mesFactures = $stmtF->fetchAll();

$nbAttente = count(array_filter($mesFactures, fn($f) => $f['statut'] === 'en_attente'));
$nbEnCours = count(array_filter($mesFactures, fn($f) => $f['statut'] === 'en_cours'));
$nbReglees = count(array_filter($mesFactures, fn($f) => $f['statut'] === 'reglee'));
$montantTotal = array_sum(array_column($mesFactures, 'montant_fac'));
?>

<h1>Bonjour, <?= e($user['prenom']) ?></h1>
<p style="color:var(--ink-600); margin-top:-8px; margin-bottom:24px;">Voici le suivi de vos contrats et factures avec SONATRACH.</p>

<div class="kpi-row">
    <div class="kpi">
        <span class="value mono"><?= (int)$nbContrats ?></span>
        <span class="label">Contrats en cours</span>
    </div>
    <div class="kpi">
        <span class="value mono"><?= $nbAttente ?></span>
        <span class="label">Factures en attente</span>
    </div>
    <div class="kpi flame">
        <span class="value mono"><?= $nbReglees ?></span>
        <span class="label">Factures réglées</span>
    </div>
    <div class="kpi">
        <span class="value mono"><?= fmt_montant($montantTotal) ?></span>
        <span class="label">Montant total facturé</span>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Mes dernières factures</h2>
        <a href="factures.php" class="btn outline small">Voir tout</a>
    </div>
    <?php if (empty($mesFactures)): ?>
        <div class="empty-state">Aucune facture enregistrée pour le moment.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th class="num">Montant</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($mesFactures, 0, 5) as $f): ?>
            <tr>
                <td class="num"><a href="facture_view.php?id=<?= $f['num_fac'] ?>">FAC-<?= str_pad($f['num_fac'], 4, '0', STR_PAD_LEFT) ?></a></td>
                <td class="num"><?= fmt_montant($f['montant_fac']) ?></td>
                <td><span class="badge <?= statut_fac_couleur($f['statut']) ?>"><?= e(statut_fac_label($f['statut'])) ?></span></td>
                <td><?= fmt_date($f['date_fac']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
