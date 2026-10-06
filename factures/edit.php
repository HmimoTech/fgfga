<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur', 'gestionnaire_factures']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM factures WHERE num_fac = ?");
$stmt->execute([$id]);
$f = $stmt->fetch();
if (!$f) { header('Location: list.php'); exit; }

$page_title = 'Modifier facture';
$page_crumb = 'Paiement / Factures / Modifier';
$error = '';
$clients = $pdo->query("SELECT num_cl, rs_cl FROM clients ORDER BY rs_cl")->fetchAll();
$fournisseurs = $pdo->query("SELECT id_four, rs_four FROM fournisseurs ORDER BY rs_four")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = $_POST['montant_fac'] ?? '';
    $date_fac = $_POST['date_fac'] ?? '';
    $num_cl = (int)($_POST['num_cl'] ?? 0);
    $id_four = (int)($_POST['id_four'] ?? 0);

    if ($montant === '' || $date_fac === '' || !$num_cl || !$id_four) {
        $error = "Montant, date, client et fournisseur sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("UPDATE factures SET date_fac=?, ddp_fac=?, montant_fac=?, num_cl=?, id_four=? WHERE num_fac=?");
        $stmt->execute([
            $date_fac,
            $_POST['ddp_fac'] ?: null,
            $montant,
            $num_cl,
            $id_four,
            $id
        ]);
        log_activite('Modification facture', "num_fac=$id");
        header('Location: view.php?id=' . $id);
        exit;
    }
    $f = array_merge($f, $_POST);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <h2>Modifier facture FAC-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field">
                <label>Client *</label>
                <select name="num_cl" required>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['num_cl'] ?>" <?= $c['num_cl'] == $f['num_cl'] ? 'selected' : '' ?>><?= e($c['rs_cl']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Fournisseur *</label>
                <select name="id_four" required>
                    <?php foreach ($fournisseurs as $fo): ?>
                        <option value="<?= $fo['id_four'] ?>" <?= $fo['id_four'] == $f['id_four'] ? 'selected' : '' ?>><?= e($fo['rs_four']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Date de la facture *</label>
                <input type="date" name="date_fac" required value="<?= e($f['date_fac']) ?>">
            </div>
            <div class="field">
                <label>Date de dépôt</label>
                <input type="date" name="ddp_fac" value="<?= e($f['ddp_fac']) ?>">
            </div>
            <div class="field">
                <label>Montant (DA) *</label>
                <input type="number" step="0.01" name="montant_fac" required value="<?= e($f['montant_fac']) ?>">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="view.php?id=<?= $id ?>" class="btn outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
