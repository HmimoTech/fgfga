<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
try {
    $stmt = $pdo->prepare("DELETE FROM fournisseurs WHERE id_four = ?");
    $stmt->execute([$id]);
    log_activite('Suppression fournisseur', "id_four=$id");
} catch (PDOException $e) {
    // Contrainte de clé étrangère probable
}
header('Location: list.php');
exit;
