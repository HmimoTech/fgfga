<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur']);

$page_title = 'Nouveau bon de paiement';
$page_crumb = 'Paiement / Bons de paiement / Nouveau';
$clients = $pdo->query("SELECT num_cl, rs_cl FROM clients ORDER BY rs_cl")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO bons_paiement (date_creation_bp, num_cl, observation) VALUES (?, ?, ?)");
    $stmt->execute([
        date('Y-m-d'),
        $_POST['num_cl'] ?: null,
        trim($_POST['observation'] ?? '')
    ]);
    $num_bp = $pdo->lastInsertId();
    log_activite('Création bon de paiement', "num_bp=$num_bp");
    header('Location: view.php?id=' . $num_bp);
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:600px;">
    <h2>Nouveau bon de paiement</h2>
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Client</label>
                <select name="num_cl">
                    <option value="">— Non spécifié —</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['num_cl'] ?>"><?= e($c['rs_cl']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field full">
                <label>Observation</label>
                <textarea name="observation"></textarea>
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="btn flame">Créer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
