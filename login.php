<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'fournisseur' ? 'espace_fournisseur/dashboard.php' : 'dashboard/index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($password, $u['mot_de_passe'])) {
        $_SESSION['user_id'] = $u['id_user'];
        $_SESSION['nom'] = $u['nom'];
        $_SESSION['prenom'] = $u['prenom'];
        $_SESSION['role'] = $u['role'];
        $_SESSION['id_four'] = $u['id_four'];
        log_activite('Connexion');
        if ($u['role'] === 'fournisseur') {
            header('Location: espace_fournisseur/dashboard.php');
        } else {
            header('Location: dashboard/index.php');
        }
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — SONATRACH Suivi Factures &amp; Contrats</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-visual">
        <div class="tag">Division PED</div>
        <h1>Suivi des factures et des contrats</h1>
        <p>Circuit numérique du bon de paiement — de l'ordonnancement au règlement, avec traçabilité complète à chaque étape.</p>
        <div class="chain">
            <span>Ordonnateur</span>
            <span>Trésorerie</span>
            <span>Comptable</span>
            <span>Réglé</span>
        </div>
    </div>
    <div class="login-form-side">
        <div class="login-box">
            <h2>Connexion</h2>
            <p class="lead">Accédez à votre espace de suivi.</p>
            <?php if ($error): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn flame">Se connecter</button>
            </form>
            <div class="test-accounts">
                Comptes de test (mot de passe : <code>password123</code>)<br>
                <code>admin@sonatrach.dz</code> · <code>ordonnateur@sonatrach.dz</code><br>
                <code>tresorerie@sonatrach.dz</code> · <code>comptable@sonatrach.dz</code><br>
                <code>gestionnaire@sonatrach.dz</code> · <code>fournisseur@enafor.dz</code>
            </div>
        </div>
    </div>
</div>
</body>
</html>
