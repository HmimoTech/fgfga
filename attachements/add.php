<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur']);

$page_title = 'Nouvel attachement';
$page_crumb = 'Marchés / Attachements / Nouveau';
$error = '';
$contrats = $pdo->query("SELECT num_contrat, objet_contrat FROM contrats ORDER BY date_signature DESC")->fetchAll();
$contratPreselect = (int)($_GET['contrat'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_contrat = (int)($_POST['num_contrat'] ?? 0);
    if (!$num_contrat) {
        $error = "Veuillez sélectionner un contrat.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO attachements (num_contrat, date_att, montant_att, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $num_contrat,
            $_POST['date_att'] ?: date('Y-m-d'),
            $_POST['montant_att'] ?: 0,
            trim($_POST['description'] ?? '')
        ]);
        log_activite('Création attachement', "contrat=$num_contrat");
        header('Location: list.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <h2>Nouvel attachement de travaux</h2>
    <?php if (empty($contrats)): ?>
        <div class="alert error">Vous devez d'abord créer un contrat. <a href="<?= base_url('contrats/add.php') ?>">Créer un contrat</a></div>
    <?php else: ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Contrat *</label>
                <select name="num_contrat" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($contrats as $c): ?>
                        <option value="<?= $c['num_contrat'] ?>" <?= $c['num_contrat'] == $contratPreselect ? 'selected' : '' ?>>
                            CT-<?= str_pad($c['num_contrat'], 4, '0', STR_PAD_LEFT) ?> — <?= e($c['objet_contrat']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Date</label>
                <input type="date" name="date_att" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="field">
                <label>Montant (DA)</label>
                <input type="number" step="0.01" name="montant_att">
            </div>
            <div class="field full">
                <label>Description</label>
                <textarea name="description"></textarea>
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
