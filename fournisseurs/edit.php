<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur', 'gestionnaire_factures']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id_four = ?");
$stmt->execute([$id]);
$f = $stmt->fetch();
if (!$f) { header('Location: list.php'); exit; }

$page_title = 'Modifier fournisseur';
$page_crumb = 'Tiers / Fournisseurs / Modifier';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rs = trim($_POST['rs_four'] ?? '');
    if ($rs === '') {
        $error = "La raison sociale est obligatoire.";
    } else {
        $stmt = $pdo->prepare("UPDATE fournisseurs SET rs_four=?, nom_four=?, pre_four=?, num_tel_four=?, adr_four=?, nis_four=?, num_fax_four=? WHERE id_four=?");
        $stmt->execute([
            $rs,
            trim($_POST['nom_four'] ?? ''),
            trim($_POST['pre_four'] ?? ''),
            trim($_POST['num_tel_four'] ?? ''),
            trim($_POST['adr_four'] ?? ''),
            trim($_POST['nis_four'] ?? ''),
            trim($_POST['num_fax_four'] ?? ''),
            $id
        ]);
        log_activite('Modification fournisseur', $rs);
        header('Location: list.php');
        exit;
    }
    $f = array_merge($f, $_POST);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:700px;">
    <h2>Modifier fournisseur</h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Raison sociale *</label>
                <input type="text" name="rs_four" required value="<?= e($f['rs_four']) ?>">
            </div>
            <div class="field">
                <label>Nom du contact</label>
                <input type="text" name="nom_four" value="<?= e($f['nom_four']) ?>">
            </div>
            <div class="field">
                <label>Prénom du contact</label>
                <input type="text" name="pre_four" value="<?= e($f['pre_four']) ?>">
            </div>
            <div class="field">
                <label>Téléphone</label>
                <input type="text" name="num_tel_four" value="<?= e($f['num_tel_four']) ?>">
            </div>
            <div class="field">
                <label>Fax</label>
                <input type="text" name="num_fax_four" value="<?= e($f['num_fax_four']) ?>">
            </div>
            <div class="field">
                <label>NIS</label>
                <input type="text" name="nis_four" value="<?= e($f['nis_four']) ?>">
            </div>
            <div class="field full">
                <label>Adresse</label>
                <input type="text" name="adr_four" value="<?= e($f['adr_four']) ?>">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
