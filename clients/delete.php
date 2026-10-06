<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
try {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE num_cl = ?");
    $stmt->execute([$id]);
    log_activite('Suppression client', "num_cl=$id");
} catch (PDOException $e) {
    // Probablement une contrainte de clé étrangère (client référencé ailleurs)
}
header('Location: list.php');
exit;
