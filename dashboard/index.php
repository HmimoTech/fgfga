<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/workflow.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);
$user = current_user();

$page_title = 'Tableau de bord';
$page_crumb = 'Tableau de bord';

// KPIs
$nbFacturesAttente = $pdo->query("SELECT COUNT(*) FROM factures WHERE statut='en_attente'")->fetchColumn();
$nbFacturesEnCours = $pdo->query("SELECT COUNT(*) FROM factures WHERE statut='en_cours'")->fetchColumn();
$nbFacturesReglees = $pdo->query("SELECT COUNT(*) FROM factures WHERE statut='reglee'")->fetchColumn();
$montantTotal = $pdo->query("SELECT COALESCE(SUM(montant_fac),0) FROM factures")->fetchColumn();
$nbContrats = $pdo->query("SELECT COUNT(*) FROM contrats")->fetchColumn();

// Bons de paiement en attente d'une action pour le rôle courant
$role = $user['role'];
$bpEnAttente = [];
if (in_array($role, ['ordonnateur','tresorerie','comptable','admin'])) {
    $stmt = $pdo->query("
        SELECT bp.*, c.rs_cl
        FROM bons_paiement bp
        LEFT JOIN clients c ON c.num_cl = bp.num_cl
        WHERE bp.statut NOT IN ('regle')
        ORDER BY bp.date_creation_bp DESC
        LIMIT 30
    ");
    $all = $stmt->fetchAll();
    foreach ($all as $bp) {
        if (!empty(actions_possibles_bp($bp['statut'], $role))) {
            $bpEnAttente[] = $bp;
        }
    }
}

// Dernières factures
$dernieresFactures = $pdo->query("
    SELECT f.*, c.rs_cl, fo.rs_four
    FROM factures f
    LEFT JOIN clients c ON c.num_cl = f.num_cl
    LEFT JOIN fournisseurs fo ON fo.id_four = f.id_four
    ORDER BY f.date_creation DESC
    LIMIT 6
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Bonjour, <?= e($user['prenom']) ?></h1>
<p style="color:var(--ink-600); margin-top:-8px; margin-bottom:24px;">Voici l'état actuel du suivi des factures et contrats — <?= e(role_label($role)) ?>.</p>

<div class="kpi-row">
    <div class="kpi">
        <span class="value mono"><?= (int)$nbFacturesAttente ?></span>
        <span class="label">Factures en attente</span>
    </div>
    <div class="kpi">
        <span class="value mono"><?= (int)$nbFacturesEnCours ?></span>
        <span class="label">Factures en cours</span>
    </div>
    <div class="kpi flame">
        <span class="value mono"><?= (int)$nbFacturesReglees ?></span>
        <span class="label">Factures réglées</span>
    </div>
    <div class="kpi">
        <span class="value mono"><?= fmt_montant($montantTotal) ?></span>
        <span class="label">Montant total facturé</span>
    </div>
</div>

<?php if (!empty($bpEnAttente)): ?>
<div class="card">
    <div class="card-head">
        <h2>Bons de paiement en attente de votre action</h2>
        <a href="<?= base_url('bons_paiement/list.php') ?>" class="btn outline small">Voir tout</a>
    </div>
    <table>
        <thead>
            <tr><th>N° BP</th><th>Client</th><th>Statut</th><th>Créé le</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($bpEnAttente as $bp): ?>
            <tr>
                <td class="num">BP-<?= str_pad($bp['num_bp'], 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($bp['rs_cl']) ?></td>
                <td><span class="badge <?= statut_bp_couleur($bp['statut']) ?>"><?= e(statut_bp_label($bp['statut'])) ?></span></td>
                <td><?= fmt_date($bp['date_creation_bp']) ?></td>
                <td><a href="<?= base_url('bons_paiement/view.php?id=' . $bp['num_bp']) ?>" class="btn small outline">Traiter</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-head">
        <h2>Dernières factures</h2>
        <a href="<?= base_url('factures/list.php') ?>" class="btn outline small">Voir tout</a>
    </div>
    <?php if (empty($dernieresFactures)): ?>
        <div class="empty-state">Aucune facture enregistrée pour le moment.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>N° Facture</th><th>Client</th><th>Fournisseur</th><th class="num">Montant</th><th>Statut</th><th>Date</th></tr>
        </thead>
        <tbody>
            <?php foreach ($dernieresFactures as $f): ?>
            <tr>
                <td class="num"><a href="<?= base_url('factures/view.php?id=' . $f['num_fac']) ?>">FAC-<?= str_pad($f['num_fac'], 4, '0', STR_PAD_LEFT) ?></a></td>
                <td><?= e($f['rs_cl']) ?></td>
                <td><?= e($f['rs_four']) ?></td>
                <td class="num"><?= fmt_montant($f['montant_fac']) ?></td>
                <td><span class="badge <?= statut_fac_couleur($f['statut']) ?>"><?= e(statut_fac_label($f['statut'])) ?></span></td>
                <td><?= fmt_date($f['date_fac']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="kpi-row" style="grid-template-columns: 1fr;">
    <div class="kpi">
        <span class="value mono"><?= (int)$nbContrats ?></span>
        <span class="label">Contrats enregistrés au total</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
