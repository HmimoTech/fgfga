<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur']);

$page_title = 'Nouveau contrat';
$page_crumb = 'Marchés / Contrats / Nouveau';
$error = '';

$fournisseurs = $pdo->query("SELECT id_four, rs_four FROM fournisseurs ORDER BY rs_four")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objet = trim($_POST['objet_contrat'] ?? '');
    $id_four = (int)($_POST['id_four'] ?? 0);
    if ($objet === '' || !$id_four) {
        $error = "L'objet du contrat et le fournisseur sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO contrats (objet_contrat, date_signature, montant_ht, id_four) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $objet,
            $_POST['date_signature'] ?: null,
            $_POST['montant_ht'] ?: 0,
            $id_four
        ]);
        log_activite('Création contrat', $objet);
        header('Location: list.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <h2>Nouveau contrat</h2>
    <?php if (empty($fournisseurs)): ?>
        <div class="alert error">Vous devez d'abord créer un fournisseur avant de pouvoir enregistrer un contrat. <a href="<?= base_url('fournisseurs/add.php') ?>">Créer un fournisseur</a></div>
    <?php else: ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Objet du contrat *</label>
                <input type="text" name="objet_contrat" required value="<?= e($_POST['objet_contrat'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Fournisseur *</label>
                <select name="id_four" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($fournisseurs as $f): ?>
                        <option value="<?= $f['id_four'] ?>"><?= e($f['rs_four']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Date de signature</label>
                <input type="date" name="date_signature" value="<?= e($_POST['date_signature'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Montant HT (DA)</label>
                <input type="number" step="0.01" name="montant_ht" value="<?= e($_POST['montant_ht'] ?? '') ?>">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
