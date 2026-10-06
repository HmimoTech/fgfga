<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin','ordonnateur','tresorerie','comptable','gestionnaire_factures']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id_four = ?");
$stmt->execute([$id]);
$f = $stmt->fetch();
if (!$f) { header('Location: list.php'); exit; }

$page_title = $f['rs_four'];
$page_crumb = 'Tiers / Fournisseurs / Détails';

$contrats = $pdo->prepare("SELECT * FROM contrats WHERE id_four = ? ORDER BY date_signature DESC");
$contrats->execute([$id]);
$contrats = $contrats->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card-head">
    <h1><?= e($f['rs_four']) ?></h1>
    <a href="list.php" class="btn outline small">← Retour</a>
</div>

<div class="card">
    <h2>Coordonnées</h2>
    <div class="form-grid">
        <div class="field"><label>Contact</label><div><?= e(trim($f['nom_four'] . ' ' . $f['pre_four'])) ?: '—' ?></div></div>
        <div class="field"><label>Téléphone</label><div class="mono"><?= e($f['num_tel_four']) ?: '—' ?></div></div>
        <div class="field"><label>Fax</label><div class="mono"><?= e($f['num_fax_four']) ?: '—' ?></div></div>
        <div class="field"><label>NIS</label><div class="mono"><?= e($f['nis_four']) ?: '—' ?></div></div>
        <div class="field full"><label>Adresse</label><div><?= e($f['adr_four']) ?: '—' ?></div></div>
    </div>
</div>

<div class="card">
    <h2>Contrats liés (<?= count($contrats) ?>)</h2>
    <?php if (empty($contrats)): ?>
        <div class="empty-state">Aucun contrat pour ce fournisseur.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Objet</th><th>Signature</th><th class="num">Montant HT</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($contrats as $c): ?>
            <tr>
                <td><?= e($c['objet_contrat']) ?></td>
                <td><?= fmt_date($c['date_signature']) ?></td>
                <td class="num"><?= fmt_montant($c['montant_ht']) ?></td>
                <td><a href="<?= base_url('contrats/view.php?id=' . $c['num_contrat']) ?>" class="btn outline small">Voir</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
