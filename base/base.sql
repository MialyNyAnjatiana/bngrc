-- ============================================
-- CREATION DES TABLES
-- ============================================

-- TABLE USER
CREATE TABLE IF NOT EXISTS User (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    mdp VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL
);

-- TABLE VILLE
CREATE TABLE IF NOT EXISTS ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL
);

-- TABLE CATEGORIE
CREATE TABLE IF NOT EXISTS categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

-- TABLE UNITE
CREATE TABLE IF NOT EXISTS unite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    symbole VARCHAR(20) NOT NULL,
    type VARCHAR(20) NOT NULL -- 'nature', 'materiau', 'monnaie'
);

-- TABLE BESOIN
CREATE TABLE IF NOT EXISTS besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idville INT NOT NULL,
    idcategorie INT NOT NULL,
    description TEXT NOT NULL,
    quantite INT NOT NULL,
    quantite_recue INT DEFAULT 0,
    id_unite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NULL,
    ordre INT NULL DEFAULT 0,
    date_besoin DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    
    FOREIGN KEY (idville) REFERENCES ville(id),
    FOREIGN KEY (idcategorie) REFERENCES categorie(id),
    FOREIGN KEY (id_unite) REFERENCES unite(id)
);

-- TABLE DONS
CREATE TABLE IF NOT EXISTS dons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idbesoin INT NULL,
    idcategorie INT NULL,
    description TEXT NOT NULL,
    quantite INT NOT NULL,
    valeur DECIMAL(10,2) NULL,
    prix_unitaire DECIMAL(10,2) NULL,
    id_unite INT NOT NULL,
    montant_restant DECIMAL(10,2) NULL,
    idville_attribuee INT NULL,
    date_don DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    
    FOREIGN KEY (idbesoin) REFERENCES besoin(id),
    FOREIGN KEY (idcategorie) REFERENCES categorie(id),
    FOREIGN KEY (id_unite) REFERENCES unite(id),
    FOREIGN KEY (idville_attribuee) REFERENCES ville(id)
);

-- TABLE ACHAT
CREATE TABLE IF NOT EXISTS achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iddon INT NOT NULL,
    idbesoin INT NOT NULL,
    quantite INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    
    FOREIGN KEY (iddon) REFERENCES dons(id),
    FOREIGN KEY (idbesoin) REFERENCES besoin(id)
);

-- TABLE VENTE
CREATE TABLE IF NOT EXISTS vente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iddon INT NOT NULL,
    montant_vente DECIMAL(10,2) NOT NULL,
    montant_recupere DECIMAL(10,2) NOT NULL,
    pourcentage_applique DECIMAL(5,2) NOT NULL,
    date_vente DATETIME DEFAULT CURRENT_TIMESTAMP,
    description TEXT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    
    FOREIGN KEY (iddon) REFERENCES dons(id)
);

-- TABLE PARAMETRES
CREATE TABLE IF NOT EXISTS parametres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(50) NOT NULL UNIQUE,
    valeur VARCHAR(255) NOT NULL,
    description TEXT NULL,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- INSERTION DES DONNEES DE BASE
-- ============================================

-- Insertion des unités
INSERT INTO unite (nom, symbole, type) VALUES
-- Unités pour nature
('Kilogramme', 'kg', 'nature'),
('Gramme', 'g', 'nature'),
('Litre', 'L', 'nature'),
('Sac', 'sac', 'nature'),
('Carton', 'carton', 'nature'),
('Unité', 'u', 'nature'),
('Paquet', 'pqt', 'nature'),
-- Unités pour matériaux
('Pièce', 'pièce', 'materiau'),
('Mètre', 'm', 'materiau'),
('Mètre carré', 'm²', 'materiau'),
('Mètre cube', 'm³', 'materiau'),
('Tonne', 't', 'materiau'),
('Rouleau', 'rl', 'materiau'),
-- Unités pour monnaie
('Ariary', 'Ar', 'monnaie'),
('Euro', '€', 'monnaie'),
('Dollar', '$', 'monnaie');

-- Insertion des catégories
INSERT INTO categorie (nom) VALUES
('En nature'),
('En matériaux'),
('En argent');

-- ============================================
-- INSERTION DES VILLES
-- ============================================
INSERT INTO ville (nom, region) VALUES
('Toamasina', 'Atsinanana'),
('Mananjary', 'Vatovavy'),
('Farafangana', 'Atsimo-Atsinanana'),
('Nosy Be', 'Diana'),
('Morondava', 'Menabe');

-- ============================================
-- INSERTION DES BESOINS
-- ============================================
INSERT INTO besoin (id, idville, idcategorie, description, quantite, quantite_recue, id_unite, prix_unitaire, ordre, date_besoin, is_default) VALUES
(2, 1, 1, 'Riz (kg)', 800, 0, 1, 3000, 17, '2026-02-16', 0),
(3, 1, 1, 'Eau (L)', 1500, 0, 3, 1000, 4, '2026-02-15', 0),
(4, 1, 2, 'Tôle', 120, 0, 8, 25000, 23, '2026-02-16', 0),
(5, 1, 2, 'Bâche', 200, 0, 8, 15000, 1, '2026-02-15', 0),
(6, 1, 3, 'Argent', 12000000, 0, 14, 1, 12, '2026-02-16', 0),
(7, 2, 1, 'Riz (kg)', 500, 0, 1, 3000, 9, '2026-02-15', 0),
(8, 2, 1, 'Huile (L)', 120, 0, 3, 6000, 25, '2026-02-16', 0),
(9, 2, 2, 'Tôle', 80, 0, 8, 25000, 6, '2026-02-15', 0),
(10, 2, 2, 'Clous (kg)', 60, 0, 1, 8000, 19, '2026-02-16', 0),
(11, 2, 3, 'Argent', 6000000, 0, 14, 1, 3, '2026-02-15', 0),
(12, 3, 1, 'Riz (kg)', 600, 0, 1, 3000, 21, '2026-02-16', 0),
(13, 3, 1, 'Eau (L)', 1000, 0, 3, 1000, 14, '2026-02-15', 0),
(14, 3, 2, 'Bâche', 150, 0, 8, 15000, 8, '2026-02-16', 0),
(15, 3, 2, 'Bois', 100, 0, 8, 10000, 26, '2026-02-15', 0),
(16, 3, 3, 'Argent', 8000000, 0, 14, 1, 10, '2026-02-16', 0),
(17, 4, 1, 'Riz (kg)', 300, 0, 1, 3000, 5, '2026-02-15', 0),
(18, 4, 1, 'Haricots', 200, 0, 1, 4000, 18, '2026-02-16', 0),
(19, 4, 2, 'Tôle', 40, 0, 8, 25000, 2, '2026-02-15', 0),
(20, 4, 2, 'Clous (kg)', 30, 0, 1, 8000, 24, '2026-02-16', 0),
(21, 4, 3, 'Argent', 4000000, 0, 14, 1, 7, '2026-02-15', 0),
(22, 5, 1, 'Riz (kg)', 700, 0, 1, 3000, 11, '2026-02-16', 0),
(23, 5, 1, 'Eau (L)', 1200, 0, 3, 1000, 20, '2026-02-15', 0),
(24, 5, 2, 'Bâche', 180, 0, 8, 15000, 15, '2026-02-16', 0),
(25, 5, 2, 'Bois', 150, 0, 8, 10000, 22, '2026-02-15', 0),
(26, 5, 3, 'Argent', 10000000, 0, 14, 1, 13, '2026-02-16', 0),
(27, 1, 2, 'groupe', 3, 0, 8, 6750000, 16, '2026-02-15', 0);

-- ============================================
-- INSERTION DES DONS
-- ============================================
INSERT INTO dons (id, idbesoin, idcategorie, description, quantite, valeur, prix_unitaire, id_unite, montant_restant, idville_attribuee, date_don, is_default) VALUES
(2, NULL, 3, 'Argent', 5000000, 5000000, 1, 14, NULL, NULL, '2026-02-16', 0),
(3, NULL, 3, 'Argent', 3000000, 3000000, 1, 14, NULL, NULL, '2026-02-16', 0),
(4, NULL, 3, 'Argent', 4000000, 4000000, 1, 14, NULL, NULL, '2026-02-17', 0),
(5, NULL, 3, 'Argent', 1500000, 1500000, 1, 14, NULL, NULL, '2026-02-17', 0),
(6, NULL, 3, 'Argent', 6000000, 6000000, 1, 14, NULL, NULL, '2026-02-17', 0),
(7, NULL, 1, 'Riz (kg)', 400, NULL, NULL, 1, NULL, NULL, '2026-02-16', 0),
(8, NULL, 1, 'Eau (L)', 600, NULL, NULL, 3, NULL, NULL, '2026-02-16', 0),
(9, NULL, 2, 'Tôle', 50, NULL, NULL, 8, NULL, NULL, '2026-02-17', 0),
(10, NULL, 2, 'Bâche', 70, NULL, NULL, 8, NULL, NULL, '2026-02-17', 0),
(11, NULL, 1, 'Haricots', 100, NULL, NULL, 1, NULL, NULL, '2026-02-17', 0),
(12, NULL, 1, 'Riz (kg)', 2000, NULL, NULL, 1, NULL, NULL, '2026-02-18', 0),
(13, NULL, 2, 'Tôle', 300, NULL, NULL, 8, NULL, NULL, '2026-02-18', 0),
(14, NULL, 1, 'Eau (L)', 5000, NULL, NULL, 3, NULL, NULL, '2026-02-18', 0),
(15, NULL, 3, 'Argent', 20000000, 20000000, 1, 14, NULL, NULL, '2026-02-19', 0),
(16, NULL, 2, 'Bâche', 500, NULL, NULL, 8, NULL, NULL, '2026-02-19', 0),
(17, NULL, 1, 'Haricots', 88, NULL, NULL, 1, NULL, NULL, '2026-02-17', 0);

-- ============================================
-- RÉINITIALISATION DES COMPTEURS AUTO_INCREMENT
-- ============================================
-- Pour besoin
SELECT MAX(id) INTO @maxId FROM besoin;
SET @newAutoIncrement = @maxId + 1;
SET @sql = CONCAT('ALTER TABLE besoin AUTO_INCREMENT = ', @newAutoIncrement);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Pour dons
SELECT MAX(id) INTO @maxId FROM dons;
SET @newAutoIncrement = @maxId + 1;
SET @sql = CONCAT('ALTER TABLE dons AUTO_INCREMENT = ', @newAutoIncrement);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


ALTER table besoin
ALTER COLUMN is_default SET DEFAULT 1;

-- Add is_default to DONS
ALTER TABLE dons
ALTER COLUMN is_default SET DEFAULT 1;

-- Add is_default to ACHAT
ALTER TABLE achat
ALTER COLUMN is_default SET DEFAULT 1;

ALTER TABLE vente
ALTER COLUMN is_default SET DEFAULT 1;

-- Mise à jour manuelle avec les valeurs exactes
UPDATE dons SET prix_unitaire = 3000 WHERE description = 'Riz (kg)' AND prix_unitaire IS NULL;
UPDATE dons SET prix_unitaire = 1000 WHERE description = 'Eau (L)' AND prix_unitaire IS NULL;
UPDATE dons SET prix_unitaire = 25000 WHERE description = 'Tôle' AND prix_unitaire IS NULL;
UPDATE dons SET prix_unitaire = 15000 WHERE description = 'Bâche' AND prix_unitaire IS NULL;
UPDATE dons SET prix_unitaire = 4000 WHERE description = 'Haricots' AND prix_unitaire IS NULL;
UPDATE dons SET prix_unitaire = 8000 WHERE description = 'Clous (kg)' AND prix_unitaire IS NULL;
UPDATE dons SET prix_unitaire = 10000 WHERE description = 'Bois' AND prix_unitaire IS NULL;
UPDATE dons SET prix_unitaire = 6000 WHERE description = 'Huile (L)' AND prix_unitaire IS NULL;
