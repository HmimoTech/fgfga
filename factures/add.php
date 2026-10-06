<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur', 'gestionnaire_factures']);

$page_title = 'Nouvelle facture';
$page_crumb = 'Paiement / Factures / Nouvelle';
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
        $stmt = $pdo->prepare("INSERT INTO factures (date_fac, ddp_fac, montant_fac, num_cl, id_four) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $date_fac,
            $_POST['ddp_fac'] ?: null,
            $montant,
            $num_cl,
            $id_four,
        ]);
        $num_fac = $pdo->lastInsertId();
        log_activite('Création facture', "num_fac=$num_fac");
        header('Location: view.php?id=' . $num_fac);
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <h2>Nouvelle facture</h2>
    <?php if (empty($clients) || empty($fournisseurs)): ?>
        <div class="alert error">Vous devez d'abord créer un client et un fournisseur.</div>
    <?php else: ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field">
                <label>Client *</label>
                <select name="num_cl" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['num_cl'] ?>"><?= e($c['rs_cl']) ?></option>
                    <?php endforeach; ?>
                </select>
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
                <label>Date de la facture *</label>
                <input type="date" name="date_fac" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="field">
                <label>Date de dépôt</label>
                <input type="date" name="ddp_fac">
            </div>
            <div class="field">
                <label>Montant (DA) *</label>
                <input type="number" step="0.01" name="montant_fac" required>
            </div>
        </div>
        <p style="color:var(--ink-600); font-size:0.85rem;">Le bon de paiement associé pourra être créé et lié ensuite depuis la fiche de la facture.</p>
        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
