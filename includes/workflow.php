<?php
/**
 * Définition du circuit du bon de paiement et des libellés/couleurs associés.
 */

function statut_bp_label($statut) {
    $labels = [
        'cree'                 => 'Créé',
        'envoye_ordonnateur'   => 'Envoyé à l\'ordonnateur',
        'vise_ordonnateur'     => 'Visé par l\'ordonnateur',
        'remis_tresorerie'     => 'Remis à la trésorerie',
        'vise_comptable'       => 'Visé par le comptable',
        'retourne_ordonnateur' => 'Retourné (ordonnateur)',
        'retourne_tresorerie'  => 'Retourné (trésorerie)',
        'regle'                => 'Réglé',
    ];
    return $labels[$statut] ?? $statut;
}

function statut_bp_couleur($statut) {
    $couleurs = [
        'cree'                 => 'neutre',
        'envoye_ordonnateur'   => 'attente',
        'vise_ordonnateur'     => 'progres',
        'remis_tresorerie'     => 'progres',
        'vise_comptable'       => 'progres',
        'retourne_ordonnateur' => 'alerte',
        'retourne_tresorerie'  => 'alerte',
        'regle'                => 'succes',
    ];
    return $couleurs[$statut] ?? 'neutre';
}

function statut_fac_label($statut) {
    $labels = [
        'en_attente' => 'En attente',
        'en_cours'   => 'En cours',
        'reglee'     => 'Réglée',
    ];
    return $labels[$statut] ?? $statut;
}

function statut_fac_couleur($statut) {
    $couleurs = [
        'en_attente' => 'attente',
        'en_cours'   => 'progres',
        'reglee'     => 'succes',
    ];
    return $couleurs[$statut] ?? 'neutre';
}

/**
 * Détermine les actions possibles sur un bon de paiement selon le rôle
 * de l'utilisateur courant et le statut actuel du bon.
 * Retourne un tableau d'actions : ['action_key' => 'Libellé bouton']
 */
function actions_possibles_bp($statut, $role) {
    $actions = [];

    // Note : chaque cas ci-dessous autorise déjà explicitement le rôle 'admin',
    // qui peut donc intervenir à n'importe quelle étape du circuit.
    switch ($statut) {
        case 'cree':
            if (in_array($role, ['ordonnateur', 'admin'])) {
                $actions['envoyer_ordonnateur'] = "Envoyer à l'ordonnateur";
            }
            break;
        case 'envoye_ordonnateur':
            if (in_array($role, ['ordonnateur', 'admin'])) {
                $actions['viser_ordonnateur'] = "Viser (ordonnateur)";
                $actions['retourner_ordonnateur'] = "Retourner";
            }
            break;
        case 'vise_ordonnateur':
            if (in_array($role, ['ordonnateur', 'admin'])) {
                $actions['remettre_tresorerie'] = "Remettre à la trésorerie";
            }
            break;
        case 'remis_tresorerie':
            if (in_array($role, ['tresorerie', 'admin'])) {
                $actions['transmettre_comptable'] = "Transmettre au comptable";
                $actions['retourner_tresorerie'] = "Retourner";
            }
            break;
        case 'vise_comptable':
            if (in_array($role, ['comptable', 'admin'])) {
                $actions['regler'] = "Marquer comme réglé";
            }
            break;
        case 'retourne_ordonnateur':
            if (in_array($role, ['ordonnateur', 'admin'])) {
                $actions['renvoyer_ordonnateur'] = "Renvoyer à l'ordonnateur";
            }
            break;
        case 'retourne_tresorerie':
            if (in_array($role, ['tresorerie', 'admin'])) {
                $actions['renvoyer_tresorerie'] = "Renvoyer à la trésorerie";
            }
            break;
    }
    return $actions;
}

/**
 * Applique une transition sur un bon de paiement.
 */
function appliquer_action_bp($pdo, $num_bp, $action, $id_user) {
    $today = date('Y-m-d');
    switch ($action) {
        case 'envoyer_ordonnateur':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='envoye_ordonnateur', date_envoi_ord=? WHERE num_bp=?");
            $stmt->execute([$today, $num_bp]);
            break;
        case 'viser_ordonnateur':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='vise_ordonnateur', date_arrivee_ord=?, visa_ord=1, id_user_ordonnateur=? WHERE num_bp=?");
            $stmt->execute([$today, $id_user, $num_bp]);
            break;
        case 'retourner_ordonnateur':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='retourne_ordonnateur', date_retour_ord=? WHERE num_bp=?");
            $stmt->execute([$today, $num_bp]);
            break;
        case 'renvoyer_ordonnateur':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='envoye_ordonnateur', date_envoi_ord=? WHERE num_bp=?");
            $stmt->execute([$today, $num_bp]);
            break;
        case 'remettre_tresorerie':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='remis_tresorerie', date_remise_tre=? WHERE num_bp=?");
            $stmt->execute([$today, $num_bp]);
            break;
        case 'transmettre_comptable':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='vise_comptable', date_arrivee_tre=?, visa_tre=1, id_user_tresorerie=? WHERE num_bp=?");
            $stmt->execute([$today, $id_user, $num_bp]);
            break;
        case 'retourner_tresorerie':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='retourne_tresorerie', date_retour_tre=? WHERE num_bp=?");
            $stmt->execute([$today, $num_bp]);
            break;
        case 'renvoyer_tresorerie':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='remis_tresorerie', date_remise_tre=? WHERE num_bp=?");
            $stmt->execute([$today, $num_bp]);
            break;
        case 'regler':
            $stmt = $pdo->prepare("UPDATE bons_paiement SET statut='regle', visa_comp=1, id_user_comptable=? WHERE num_bp=?");
            $stmt->execute([$id_user, $num_bp]);
            // Marquer la/les facture(s) liées comme réglées
            $stmt2 = $pdo->prepare("UPDATE factures SET statut='reglee', cdp_fac=? WHERE num_bp=?");
            $stmt2->execute([$today, $num_bp]);
            break;
    }
}
