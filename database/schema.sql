-- ============================================================
-- Suivi des Factures et Contrats — SONATRACH Division PED
-- Script de création de la base de données + données de test
-- ============================================================

CREATE DATABASE IF NOT EXISTS suivi_factures_contrats
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE suivi_factures_contrats;

-- ============================================================
-- CLIENTS
-- ============================================================
CREATE TABLE clients (
    num_cl      INT AUTO_INCREMENT PRIMARY KEY,
    rs_cl       VARCHAR(150) NOT NULL,
    div_cl      VARCHAR(100),
    adr_cl      VARCHAR(200),
    wilaya_cl   VARCHAR(50),
    commune_cl  VARCHAR(50),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- FOURNISSEURS
-- ============================================================
CREATE TABLE fournisseurs (
    id_four        INT AUTO_INCREMENT PRIMARY KEY,
    rs_four        VARCHAR(150) NOT NULL,
    nom_four       VARCHAR(100),
    pre_four       VARCHAR(100),
    num_tel_four   VARCHAR(20),
    adr_four       VARCHAR(200),
    nis_four       VARCHAR(30),
    num_fax_four   VARCHAR(20),
    date_creation  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- UTILISATEURS & RÔLES
-- Acteurs : Administrateur, Ordonnateur, Chargé de trésorerie,
-- Comptable, Gestionnaire de factures, Fournisseur (accès externe limité)
-- ============================================================
CREATE TABLE utilisateurs (
    id_user        INT AUTO_INCREMENT PRIMARY KEY,
    nom            VARCHAR(50) NOT NULL,
    prenom         VARCHAR(50) NOT NULL,
    email          VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe   VARCHAR(255) NOT NULL,
    role           ENUM('admin','ordonnateur','tresorerie','comptable','gestionnaire_factures','fournisseur') NOT NULL,
    id_four        INT NULL,  -- renseigné uniquement pour les comptes de rôle "fournisseur" : le tiers qu'ils représentent
    actif          TINYINT(1) DEFAULT 1,
    date_creation  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_four) REFERENCES fournisseurs(id_four)
) ENGINE=InnoDB;

-- ============================================================
-- CONTRATS  (un contrat = un seul fournisseur)
-- ============================================================
CREATE TABLE contrats (
    num_contrat    INT AUTO_INCREMENT PRIMARY KEY,
    objet_contrat  VARCHAR(255) NOT NULL,
    date_signature DATE,
    montant_ht     DECIMAL(15,2),
    id_four        INT NOT NULL,
    date_creation  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_four) REFERENCES fournisseurs(id_four)
) ENGINE=InnoDB;

-- ============================================================
-- BONS DE COMMANDE  (un BC concerne un seul contrat)
-- ============================================================
CREATE TABLE bons_commande (
    num_bc        INT AUTO_INCREMENT PRIMARY KEY,
    date_bc       DATE,
    num_contrat   INT NOT NULL,
    FOREIGN KEY (num_contrat) REFERENCES contrats(num_contrat)
) ENGINE=InnoDB;

-- ============================================================
-- PRESTATIONS  (un BC contient plusieurs prestations)
-- ============================================================
CREATE TABLE prestations (
    id_prestation INT AUTO_INCREMENT PRIMARY KEY,
    num_bc        INT NOT NULL,
    designation   VARCHAR(255) NOT NULL,
    quantite      DECIMAL(10,2) DEFAULT 1,
    prix_unitaire DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (num_bc) REFERENCES bons_commande(num_bc) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ATTACHEMENTS DE TRAVAUX  (concerne un seul contrat)
-- ============================================================
CREATE TABLE attachements (
    id_attachement INT AUTO_INCREMENT PRIMARY KEY,
    num_contrat    INT NOT NULL,
    date_att       DATE,
    montant_att    DECIMAL(15,2),
    description    TEXT,
    FOREIGN KEY (num_contrat) REFERENCES contrats(num_contrat)
) ENGINE=InnoDB;

-- ============================================================
-- BONS DE PAIEMENT — le circuit de validation
-- ============================================================
CREATE TABLE bons_paiement (
    num_bp              INT AUTO_INCREMENT PRIMARY KEY,
    date_creation_bp    DATE NOT NULL,
    statut              ENUM(
                            'cree',
                            'envoye_ordonnateur',
                            'vise_ordonnateur',
                            'remis_tresorerie',
                            'vise_comptable',
                            'retourne_ordonnateur',
                            'retourne_tresorerie',
                            'regle'
                        ) DEFAULT 'cree',
    date_envoi_ord      DATE,
    date_arrivee_ord    DATE,
    date_retour_ord     DATE,
    visa_ord            TINYINT(1) DEFAULT 0,
    date_remise_tre     DATE,
    date_arrivee_tre    DATE,
    date_retour_tre     DATE,
    visa_tre            TINYINT(1) DEFAULT 0,
    visa_comp           TINYINT(1) DEFAULT 0,
    observation         TEXT,
    id_user_ordonnateur INT,
    id_user_tresorerie  INT,
    id_user_comptable   INT,
    num_cl              INT,
    FOREIGN KEY (id_user_ordonnateur) REFERENCES utilisateurs(id_user),
    FOREIGN KEY (id_user_tresorerie)  REFERENCES utilisateurs(id_user),
    FOREIGN KEY (id_user_comptable)   REFERENCES utilisateurs(id_user),
    FOREIGN KEY (num_cl) REFERENCES clients(num_cl)
) ENGINE=InnoDB;

-- ============================================================
-- FACTURES  (une facture -> un seul bon de paiement)
-- ============================================================
CREATE TABLE factures (
    num_fac      INT AUTO_INCREMENT PRIMARY KEY,
    date_fac     DATE NOT NULL,
    ddp_fac      DATE,
    cdp_fac      DATE,
    montant_fac  DECIMAL(15,2) NOT NULL,
    statut       ENUM('en_attente','en_cours','reglee') DEFAULT 'en_attente',
    num_cl       INT,
    num_bp       INT,
    id_four      INT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (num_cl)  REFERENCES clients(num_cl),
    FOREIGN KEY (num_bp)  REFERENCES bons_paiement(num_bp),
    FOREIGN KEY (id_four) REFERENCES fournisseurs(id_four)
) ENGINE=InnoDB;

-- ============================================================
-- JOURNAL D'ACTIVITÉ (traçabilité des actions du circuit)
-- ============================================================
CREATE TABLE journal_activite (
    id_log      INT AUTO_INCREMENT PRIMARY KEY,
    id_user     INT,
    action      VARCHAR(255) NOT NULL,
    cible       VARCHAR(100),
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user)
) ENGINE=InnoDB;

-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

INSERT INTO clients (rs_cl, div_cl, adr_cl, wilaya_cl, commune_cl) VALUES
('SONATRACH', 'Division PED', 'Route de Hassi Messaoud', 'Ouargla', 'Hassi Messaoud'),
('SONATRACH', 'Direction Gisements', 'Zone Industrielle', 'Ouargla', 'Hassi Messaoud'),
('SONATRACH', 'Direction Technique et Production', 'Siège E&P', 'Boumerdès', 'Boumerdès');

INSERT INTO fournisseurs (rs_four, nom_four, pre_four, num_tel_four, adr_four, nis_four, num_fax_four) VALUES
('ENAFOR SPA', 'Boudiaf', 'Karim', '029612345', 'Zone Industrielle, Hassi Messaoud', '0980000123456', '029612346'),
('ENTP SPA', 'Meziane', 'Yacine', '029654321', 'Route Nationale 3, Ouargla', '0980000654321', '029654322'),
('GCB SPA', 'Cherif', 'Amel', '023112233', 'Cité El Djorf, Boumerdès', '0980000998877', '023112234');

-- Mots de passe en clair pour les tests : "password123"
-- Hash bcrypt correspondant (compatible password_verify() de PHP) :
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, id_four) VALUES
('Belaid', 'Abderrahmane', 'admin@sonatrach.dz', '$2b$10$.UOp8KpEiRCQoxdxBFn5Me8AndmAagyM.qkGeXTWsJokDrLm2i1A2', 'admin', NULL),
('Gacem', 'Fadoua', 'ordonnateur@sonatrach.dz', '$2b$10$.UOp8KpEiRCQoxdxBFn5Me8AndmAagyM.qkGeXTWsJokDrLm2i1A2', 'ordonnateur', NULL),
('El Houari', 'Hichem', 'tresorerie@sonatrach.dz', '$2b$10$.UOp8KpEiRCQoxdxBFn5Me8AndmAagyM.qkGeXTWsJokDrLm2i1A2', 'tresorerie', NULL),
('Chekir', 'Mohamed Reda', 'comptable@sonatrach.dz', '$2b$10$.UOp8KpEiRCQoxdxBFn5Me8AndmAagyM.qkGeXTWsJokDrLm2i1A2', 'comptable', NULL),
('Amrani', 'Sara', 'gestionnaire@sonatrach.dz', '$2b$10$.UOp8KpEiRCQoxdxBFn5Me8AndmAagyM.qkGeXTWsJokDrLm2i1A2', 'gestionnaire_factures', NULL),
('Boudiaf', 'Karim', 'fournisseur@enafor.dz', '$2b$10$.UOp8KpEiRCQoxdxBFn5Me8AndmAagyM.qkGeXTWsJokDrLm2i1A2', 'fournisseur', 1);

INSERT INTO contrats (objet_contrat, date_signature, montant_ht, id_four) VALUES
('Travaux de forage puits HMD-142', '2026-01-15', 45000000.00, 1),
('Maintenance des installations de surface', '2026-02-20', 12500000.00, 2),
('Génie civil - extension base vie', '2026-03-10', 8300000.00, 3);

INSERT INTO bons_commande (date_bc, num_contrat) VALUES
('2026-02-01', 1),
('2026-03-01', 2);

INSERT INTO prestations (num_bc, designation, quantite, prix_unitaire) VALUES
(1, 'Forage phase 1 - 0 à 1200m', 1, 18000000.00),
(1, 'Location appareil de forage / jour', 30, 450000.00),
(2, 'Maintenance préventive pompes', 4, 350000.00),
(2, 'Pièces de rechange', 1, 2100000.00);

INSERT INTO attachements (num_contrat, date_att, montant_att, description) VALUES
(1, '2026-04-05', 22000000.00, 'Attachement n°1 - avancement phase 1 forage'),
(2, '2026-04-10', 3500000.00, 'Attachement n°1 - maintenance trimestrielle');

INSERT INTO bons_paiement (date_creation_bp, statut, date_envoi_ord, date_arrivee_ord, visa_ord, id_user_ordonnateur, num_cl) VALUES
('2026-04-12', 'vise_ordonnateur', '2026-04-12', '2026-04-13', 1, 2, 1),
('2026-04-20', 'cree', NULL, NULL, 0, NULL, 2);

INSERT INTO factures (date_fac, ddp_fac, montant_fac, statut, num_cl, num_bp, id_four) VALUES
('2026-04-08', '2026-04-10', 22000000.00, 'en_cours', 1, 1, 1),
('2026-04-18', '2026-04-19', 3500000.00, 'en_attente', 2, 2, 2);
