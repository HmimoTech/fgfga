<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$page_title = 'Utilisateurs';
$page_crumb = 'Administration / Utilisateurs';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $id_four = ($role === 'fournisseur' && !empty($_POST['id_four'])) ? (int)$_POST['id_four'] : null;

    if (!$nom || !$prenom || !$email || !$role || !$password) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($role === 'fournisseur' && !$id_four) {
        $error = "Merci de sélectionner le fournisseur représenté par ce compte.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, id_four) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hash, $role, $id_four]);
            log_activite('Création utilisateur', $email);
        } catch (PDOException $e) {
            $error = "Cet e-mail est déjà utilisé.";
        }
    }
}

if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE utilisateurs SET actif = 1 - actif WHERE id_user = ?")->execute([$tid]);
    header('Location: utilisateurs.php');
    exit;
}

$utilisateurs = $pdo->query("
    SELECT u.*, f.rs_four
    FROM utilisateurs u
    LEFT JOIN fournisseurs f ON f.id_four = u.id_four
    ORDER BY u.role, u.nom
")->fetchAll();
$fournisseurs = $pdo->query("SELECT id_four, rs_four FROM fournisseurs ORDER BY rs_four")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2>Nouvel utilisateur</h2>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" id="form-user">
        <div class="form-grid">
            <div class="field"><label>Nom</label><input type="text" name="nom" required></div>
            <div class="field"><label>Prénom</label><input type="text" name="prenom" required></div>
            <div class="field"><label>E-mail</label><input type="email" name="email" required></div>
            <div class="field">
                <label>Rôle</label>
                <select name="role" id="role-select" required>
                    <option value="admin">Administrateur</option>
                    <option value="ordonnateur">Ordonnateur</option>
                    <option value="tresorerie">Chargé de trésorerie</option>
                    <option value="comptable">Comptable</option>
                    <option value="gestionnaire_factures">Gestionnaire de factures</option>
                    <option value="fournisseur">Fournisseur</option>
                </select>
            </div>
            <div class="field" id="field-fournisseur" style="display:none;">
                <label>Fournisseur représenté</label>
                <select name="id_four">
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($fournisseurs as $f): ?>
                        <option value="<?= $f['id_four'] ?>"><?= e($f['rs_four']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Mot de passe</label><input type="password" name="password" required></div>
        </div>
        <div class="actions-row">
            <button type="submit" name="creer" value="1" class="btn flame">Créer</button>
        </div>
    </form>
</div>

<script>
document.getElementById('role-select').addEventListener('change', function() {
    document.getElementById('field-fournisseur').style.display = this.value === 'fournisseur' ? 'block' : 'none';
});
</script>

<div class="card">
    <h2>Utilisateurs (<?= count($utilisateurs) ?>)</h2>
    <table>
        <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Fournisseur lié</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= e($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e(role_label($u['role'])) ?></td>
                <td><?= e($u['rs_four'] ?? '—') ?></td>
                <td><span class="badge <?= $u['actif'] ? 'succes' : 'alerte' ?>"><?= $u['actif'] ? 'Actif' : 'Désactivé' ?></span></td>
                <td>
                    <?php if ($u['id_user'] != $user['id']): ?>
                    <a href="utilisateurs.php?toggle=<?= $u['id_user'] ?>" class="btn outline small"><?= $u['actif'] ? 'Désactiver' : 'Activer' ?></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
