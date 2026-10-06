<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur', 'gestionnaire_factures']);

$page_title = 'Nouveau client';
$page_crumb = 'Tiers / Clients / Nouveau';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rs = trim($_POST['rs_cl'] ?? '');
    if ($rs === '') {
        $error = "La raison sociale est obligatoire.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO clients (rs_cl, div_cl, adr_cl, wilaya_cl, commune_cl) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $rs,
            trim($_POST['div_cl'] ?? ''),
            trim($_POST['adr_cl'] ?? ''),
            trim($_POST['wilaya_cl'] ?? ''),
            trim($_POST['commune_cl'] ?? ''),
        ]);
        log_activite('Création client', $rs);
        header('Location: list.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <h2>Nouveau client</h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Raison sociale *</label>
                <input type="text" name="rs_cl" required value="<?= e($_POST['rs_cl'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Division</label>
                <input type="text" name="div_cl" value="<?= e($_POST['div_cl'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Adresse</label>
                <input type="text" name="adr_cl" value="<?= e($_POST['adr_cl'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Wilaya</label>
                <input type="text" name="wilaya_cl" value="<?= e($_POST['wilaya_cl'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Commune</label>
                <input type="text" name="commune_cl" value="<?= e($_POST['commune_cl'] ?? '') ?>">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
