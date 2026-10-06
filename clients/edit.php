<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur', 'gestionnaire_factures']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM clients WHERE num_cl = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) { header('Location: list.php'); exit; }

$page_title = 'Modifier client';
$page_crumb = 'Tiers / Clients / Modifier';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rs = trim($_POST['rs_cl'] ?? '');
    if ($rs === '') {
        $error = "La raison sociale est obligatoire.";
    } else {
        $stmt = $pdo->prepare("UPDATE clients SET rs_cl=?, div_cl=?, adr_cl=?, wilaya_cl=?, commune_cl=? WHERE num_cl=?");
        $stmt->execute([
            $rs,
            trim($_POST['div_cl'] ?? ''),
            trim($_POST['adr_cl'] ?? ''),
            trim($_POST['wilaya_cl'] ?? ''),
            trim($_POST['commune_cl'] ?? ''),
            $id
        ]);
        log_activite('Modification client', $rs);
        header('Location: list.php');
        exit;
    }
    $client = array_merge($client, $_POST);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <h2>Modifier client</h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Raison sociale *</label>
                <input type="text" name="rs_cl" required value="<?= e($client['rs_cl']) ?>">
            </div>
            <div class="field">
                <label>Division</label>
                <input type="text" name="div_cl" value="<?= e($client['div_cl']) ?>">
            </div>
            <div class="field">
                <label>Adresse</label>
                <input type="text" name="adr_cl" value="<?= e($client['adr_cl']) ?>">
            </div>
            <div class="field">
                <label>Wilaya</label>
                <input type="text" name="wilaya_cl" value="<?= e($client['wilaya_cl']) ?>">
            </div>
            <div class="field">
                <label>Commune</label>
                <input type="text" name="commune_cl" value="<?= e($client['commune_cl']) ?>">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
