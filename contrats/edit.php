<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM contrats WHERE num_contrat = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header('Location: list.php'); exit; }

$page_title = 'Modifier contrat';
$page_crumb = 'Marchés / Contrats / Modifier';
$error = '';
$fournisseurs = $pdo->query("SELECT id_four, rs_four FROM fournisseurs ORDER BY rs_four")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objet = trim($_POST['objet_contrat'] ?? '');
    $id_four = (int)($_POST['id_four'] ?? 0);
    if ($objet === '' || !$id_four) {
        $error = "L'objet du contrat et le fournisseur sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("UPDATE contrats SET objet_contrat=?, date_signature=?, montant_ht=?, id_four=? WHERE num_contrat=?");
        $stmt->execute([
            $objet,
            $_POST['date_signature'] ?: null,
            $_POST['montant_ht'] ?: 0,
            $id_four,
            $id
        ]);
        log_activite('Modification contrat', $objet);
        header('Location: list.php');
        exit;
    }
    $c = array_merge($c, $_POST);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <h2>Modifier contrat</h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Objet du contrat *</label>
                <input type="text" name="objet_contrat" required value="<?= e($c['objet_contrat']) ?>">
            </div>
            <div class="field">
                <label>Fournisseur *</label>
                <select name="id_four" required>
                    <?php foreach ($fournisseurs as $f): ?>
                        <option value="<?= $f['id_four'] ?>" <?= $f['id_four'] == $c['id_four'] ? 'selected' : '' ?>><?= e($f['rs_four']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Date de signature</label>
                <input type="date" name="date_signature" value="<?= e($c['date_signature']) ?>">
            </div>
            <div class="field">
                <label>Montant HT (DA)</label>
                <input type="number" step="0.01" name="montant_ht" value="<?= e($c['montant_ht']) ?>">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
