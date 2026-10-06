# Suivi des Factures et Contrats — SONATRACH Division PED

Application PHP/MySQL de suivi des factures, contrats, bons de commande et
bons de paiement, avec circuit de validation (ordonnateur → trésorerie →
comptable) et espace dédié aux fournisseurs.

## Fonctionnalités

- Gestion des clients, fournisseurs, contrats, bons de commande et prestations
- Attachements de travaux par contrat
- Bons de paiement avec workflow de validation à plusieurs niveaux
- Factures liées aux bons de paiement (suivi du statut)
- Journal d'activité (traçabilité des actions)
- Rôles : admin, ordonnateur, trésorerie, comptable, gestionnaire de factures, fournisseur
- Espace fournisseur (consultation de ses contrats et factures)

## Prérequis

- PHP 7.4+ avec l'extension `pdo_mysql`
- MySQL ou MariaDB
- Recommandé : [XAMPP](https://www.apachefriends.org/) ou WAMP

## Installation

1. Copier le projet dans `htdocs` (XAMPP) ou le répertoire web de votre stack :
   ```
   C:\xampp\htdocs\sonatrach-factures
   ```
2. Démarrer Apache et MySQL.
3. Importer la base de données :
   - Ouvrir `http://localhost/phpmyadmin` → **Importer** → choisir `database/schema.sql`
   - Le script crée la base `suivi_factures_contrats` et des données de test.
4. Créer le fichier de configuration :
   ```
   copy config\db.example.php config\db.php
   ```
   puis adapter les identifiants si nécessaire.
5. Ouvrir `http://localhost/sonatrach-factures`

## Comptes de test

Mot de passe pour tous les comptes : `password123`

| Email | Rôle |
|---|---|
| admin@sonatrach.dz | Administrateur |
| ordonnateur@sonatrach.dz | Ordonnateur |
| tresorerie@sonatrach.dz | Chargé de trésorerie |
| comptable@sonatrach.dz | Comptable |
| gestionnaire@sonatrach.dz | Gestionnaire de factures |
| fournisseur@enafor.dz | Fournisseur (espace externe) |

## Structure

```
├── admin/               # gestion des utilisateurs
├── assets/css/          # styles
├── attachements/        # attachements de travaux
├── bons_commande/       # bons de commande + prestations
├── bons_paiement/       # bons de paiement (circuit de validation)
├── clients/             # clients
├── config/              # connexion BD (db.php non versionné)
├── contrats/            # contrats
├── dashboard/           # tableau de bord interne
├── database/            # schema.sql
├── espace_fournisseur/  # portail fournisseur
├── factures/            # factures
├── fournisseurs/        # fournisseurs
└── includes/            # auth, layout, workflow
```
