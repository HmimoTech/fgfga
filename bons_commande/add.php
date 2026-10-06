<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'ordonnateur']);

$page_title = 'Nouveau bon de commande';
$page_crumb = 'Marchés / Bons de commande / Nouveau';
$error = '';
$contrats = $pdo->query("
    SELECT c.num_contrat, c.objet_contrat, f.rs_four
    FROM contrats c JOIN fournisseurs f ON f.id_four = c.id_four
    ORDER BY c.date_signature DESC
")->fetchAll();
$contratPreselect = (int)($_GET['contrat'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_contrat = (int)($_POST['num_contrat'] ?? 0);
    $designations = $_POST['designation'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $prix = $_POST['prix_unitaire'] ?? [];

    if (!$num_contrat) {
        $error = "Veuillez sélectionner un contrat.";
    } else {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO bons_commande (date_bc, num_contrat) VALUES (?, ?)");
        $stmt->execute([$_POST['date_bc'] ?: date('Y-m-d'), $num_contrat]);
        $num_bc = $pdo->lastInsertId();

        $stmtP = $pdo->prepare("INSERT INTO prestations (num_bc, designation, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
        foreach ($designations as $i => $d) {
            $d = trim($d);
            if ($d === '') continue;
            $stmtP->execute([$num_bc, $d, $quantites[$i] ?: 1, $prix[$i] ?: 0]);
        }
        $pdo->commit();
        log_activite('Création bon de commande', "num_bc=$num_bc");
        header('Location: view.php?id=' . $num_bc);
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:760px;">
    <h2>Nouveau bon de commande</h2>
    <?php if (empty($contrats)): ?>
        <div class="alert error">Vous devez d'abord créer un contrat. <a href="<?= base_url('contrats/add.php') ?>">Créer un contrat</a></div>
    <?php else: ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field">
                <label>Contrat *</label>
                <select name="num_contrat" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($contrats as $c): ?>
                        <option value="<?= $c['num_contrat'] ?>" <?= $c['num_contrat'] == $contratPreselect ? 'selected' : '' ?>>
                            CT-<?= str_pad($c['num_contrat'], 4, '0', STR_PAD_LEFT) ?> — <?= e($c['objet_contrat']) ?> (<?= e($c['rs_four']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Date du bon de commande</label>
                <input type="date" name="date_bc" value="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <h3 style="margin-top:22px;">Prestations</h3>
        <div id="prestations-wrap">
            <div class="form-grid prestation-row" style="grid-template-columns: 2fr 1fr 1fr; margin-bottom:8px;">
                <div class="field"><label>Désignation</label><input type="text" name="designation[]"></div>
                <div class="field"><label>Quantité</label><input type="number" step="0.01" name="quantite[]" value="1"></div>
                <div class="field"><label>Prix unitaire (DA)</label><input type="number" step="0.01" name="prix_unitaire[]"></div>
            </div>
        </div>
        <button type="button" class="btn outline small" id="add-row">+ Ajouter une prestation</button>

        <div class="actions-row">
            <button type="submit" class="btn flame">Enregistrer</button>
            <a href="list.php" class="btn outline">Annuler</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('add-row')?.addEventListener('click', function() {
    const wrap = document.getElementById('prestations-wrap');
    const row = wrap.firstElementChild.cloneNode(true);
    row.querySelectorAll('input').forEach(i => { if (i.type !== 'number') i.value = ''; else i.value = i.name.includes('quantite') ? '1' : ''; });
    wrap.appendChild(row);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
