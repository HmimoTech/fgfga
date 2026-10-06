<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/workflow.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$id = (int)($_GET['id'] ?? 0);
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $stmt = $pdo->prepare("SELECT * FROM bons_paiement WHERE num_bp = ?");
    $stmt->execute([$id]);
    $bpCheck = $stmt->fetch();
    $autorisees = $bpCheck ? actions_possibles_bp($bpCheck['statut'], $user['role']) : [];
    if ($bpCheck && array_key_exists($_POST['action'], $autorisees)) {
        appliquer_action_bp($pdo, $id, $_POST['action'], $user['id']);
        log_activite('Action bon de paiement: ' . $_POST['action'], "num_bp=$id");
    }
    header('Location: view.php?id=' . $id);
    exit;
}

$stmt = $pdo->prepare("
    SELECT bp.*, c.rs_cl,
        uo.nom AS ord_nom, uo.prenom AS ord_prenom,
        ut.nom AS tre_nom, ut.prenom AS tre_prenom,
        uc.nom AS comp_nom, uc.prenom AS comp_prenom
    FROM bons_paiement bp
    LEFT JOIN clients c ON c.num_cl = bp.num_cl
    LEFT JOIN utilisateurs uo ON uo.id_user = bp.id_user_ordonnateur
    LEFT JOIN utilisateurs ut ON ut.id_user = bp.id_user_tresorerie
    LEFT JOIN utilisateurs uc ON uc.id_user = bp.id_user_comptable
    WHERE bp.num_bp = ?
");
$stmt->execute([$id]);
$bp = $stmt->fetch();
if (!$bp) { header('Location: list.php'); exit; }

$factures = $pdo->prepare("SELECT * FROM factures WHERE num_bp = ?");
$factures->execute([$id]);
$factures = $factures->fetchAll();

$actions = actions_possibles_bp($bp['statut'], $user['role']);

$etapes = [
    'cree' => 'Créé',
    'envoye_ordonnateur' => 'Envoyé ordonnateur',
    'vise_ordonnateur' => 'Visé ordonnateur',
    'remis_tresorerie' => 'Remis trésorerie',
    'vise_comptable' => 'Visé comptable',
    'regle' => 'Réglé',
];
$ordreEtapes = array_keys($etapes);
$statutActuelIndex = array_search($bp['statut'], $ordreEtapes);
$estRetour = in_array($bp['statut'], ['retourne_ordonnateur', 'retourne_tresorerie']);

$page_title = 'BP-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$page_crumb = 'Paiement / Bons de paiement / Détails';

include __DIR__ . '/../includes/header.php';
?>

<div class="card-head">
    <h1>Bon de paiement BP-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h1>
    <a href="list.php" class="btn outline small">← Retour</a>
</div>

<div class="card">
    <?php if ($estRetour): ?>
        <div class="alert error">Ce bon a été retourné : <strong><?= e(statut_bp_label($bp['statut'])) ?></strong><?= $bp['observation'] ? ' — ' . e($bp['observation']) : '' ?></div>
    <?php else: ?>
    <div class="stepper">
        <?php foreach ($etapes as $key => $label):
            $idx = array_search($key, $ordreEtapes);
            $cls = $idx < $statutActuelIndex ? 'done' : ($idx === $statutActuelIndex ? 'current' : '');
        ?>
            <div class="step <?= $cls ?>">
                <div class="step-label"><?= e($label) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="form-grid">
        <div class="field"><label>Client</label><div><?= e($bp['rs_cl'] ?? '—') ?></div></div>
        <div class="field"><label>Statut actuel</label><div><span class="badge <?= statut_bp_couleur($bp['statut']) ?>"><?= e(statut_bp_label($bp['statut'])) ?></span></div></div>
        <div class="field"><label>Créé le</label><div><?= fmt_date($bp['date_creation_bp']) ?></div></div>
    </div>

    <?php if (!empty($actions)): ?>
    <form method="post" class="actions-row" style="flex-wrap:wrap;">
        <?php foreach ($actions as $key => $label): ?>
            <button type="submit" name="action" value="<?= $key ?>" class="btn <?= str_starts_with($key, 'retourner') ? 'danger' : 'flame' ?> small"><?= e($label) ?></button>
        <?php endforeach; ?>
    </form>
    <?php elseif ($bp['statut'] !== 'regle'): ?>
        <p style="color:var(--ink-400); font-size:0.85rem; margin-top:14px;">Aucune action disponible pour votre rôle sur ce bon à ce stade.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Circuit de validation</h2>
    <div class="form-grid">
        <div class="field"><label>Envoyé à l'ordonnateur</label><div><?= fmt_date($bp['date_envoi_ord']) ?></div></div>
        <div class="field"><label>Visé par l'ordonnateur</label><div><?= $bp['visa_ord'] ? fmt_date($bp['date_arrivee_ord']) . ' — ' . e(trim($bp['ord_prenom'] . ' ' . $bp['ord_nom'])) : '—' ?></div></div>
        <div class="field"><label>Remis à la trésorerie</label><div><?= fmt_date($bp['date_remise_tre']) ?></div></div>
        <div class="field"><label>Visé par le comptable</label><div><?= $bp['visa_comp'] ? e(trim($bp['comp_prenom'] . ' ' . $bp['comp_nom'])) : '—' ?></div></div>
    </div>
</div>

<div class="card">
    <h2>Facture(s) associée(s)</h2>
    <?php if (empty($factures)): ?>
        <div class="empty-state">Aucune facture liée à ce bon de paiement.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>N°</th><th class="num">Montant</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($factures as $f): ?>
            <tr>
                <td class="num"><a href="<?= base_url('factures/view.php?id=' . $f['num_fac']) ?>">FAC-<?= str_pad($f['num_fac'], 4, '0', STR_PAD_LEFT) ?></a></td>
                <td class="num"><?= fmt_montant($f['montant_fac']) ?></td>
                <td><span class="badge <?= statut_fac_couleur($f['statut']) ?>"><?= e(statut_fac_label($f['statut'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
