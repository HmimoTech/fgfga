<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur', 'gestionnaire_factures']);

$page_title = 'Nouveau fournisseur';
$page_crumb = 'Tiers / Fournisseurs / Nouveau';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rs = trim($_POST['rs_four'] ?? '');
    if ($rs === '') {
        $error = "La raison sociale est obligatoire.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO fournisseurs (rs_four, nom_four, pre_four, num_tel_four, adr_four, nis_four, num_fax_four) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $rs,
            trim($_POST['nom_four'] ?? ''),
            trim($_POST['pre_four'] ?? ''),
            trim($_POST['num_tel_four'] ?? ''),
            trim($_POST['adr_four'] ?? ''),
            trim($_POST['nis_four'] ?? ''),
            trim($_POST['num_fax_four'] ?? ''),
        ]);
        log_activite('Création fournisseur', $rs);
        header('Location: list.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:700px;">
    <h2>Nouveau fournisseur</h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Raison sociale *</label>
                <input type="text" name="rs_four" required value="<?= e($_POST['rs_four'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Nom du contact</label>
                <input type="text" name="nom_four" value="<?= e($_POST['nom_four'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Prénom du contact</label>
                <input type="text" name="pre_four" value="<?= e($_POST['pre_four'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Téléphone</label>
                <input type="text" name="num_tel_four" value="<?= e($_POST['num_tel_four'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Fax</label>
                <input type="text" name="num_fax_four" value="<?= e($_POST['num_fax_four'] ?? '') ?>">
            </div>
            <div class="field">
                <label>NIS</label>
                <input type="text" name="nis_four" value="<?= e($_POST['nis_four'] ?? '') ?>">
            </div>
            <div class="field full">
                <label>Adresse</label>
                <input type="text" name="adr_four" value="<?= e($_POST['adr_four'] ?? '') ?>">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
