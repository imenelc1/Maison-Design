-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 04 juin 2025 à 00:56
-- Version du serveur : 8.0.31
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `maisondesign`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `IdAdmin` int NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) NOT NULL,
  `MotDePasse` varchar(255) NOT NULL,
  `DateEnregistrement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdAdmin`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`IdAdmin`, `Email`, `MotDePasse`, `DateEnregistrement`) VALUES
(1, 'admin.pass@maison-design.com', '$2y$10$3KneE/rvsEjLPrGTaDXU0.LiyZrqt2ZyfTZ/r85a.7cAX34irKYyy', '2025-03-03 10:51:13');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `IdCategorie` int NOT NULL,
  `NomCategorie` varchar(255) NOT NULL,
  PRIMARY KEY (`IdCategorie`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`IdCategorie`, `NomCategorie`) VALUES
(10, 'Tables'),
(6, 'Lits'),
(7, 'Armoires'),
(8, 'Canapés'),
(9, 'Chaises');

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `IdClient` int NOT NULL AUTO_INCREMENT,
  `NomClient` varchar(100) NOT NULL,
  `PrenomClient` varchar(100) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `MDP` varchar(255) DEFAULT NULL,
  `Adresse` varchar(255) NOT NULL,
  `DateInscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `NumTel` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`IdClient`),
  UNIQUE KEY `Email` (`Email`)
) ;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`IdClient`, `NomClient`, `PrenomClient`, `Email`, `MDP`, `Adresse`, `DateInscription`, `NumTel`) VALUES
(5, 'kernou', 'lilia', 'kernoulilia@gmail.com', '$2y$10$Uinl7LcsXGWjUJ7b2ozX.uy9S5GYJiB/ZlSnP1dWqqV1ujhBzsM9W', 'amizour', '2025-06-02 18:51:33', '0635474398'),
(4, 'lcc', 'imene', 'imenelc18@gmail.com', '$2y$10$6fo7kwYA99RJkwn2cmOgjekBrhPWf3cRySUtTAUiLShk1Ru7IFa.m', 'BEJAIA', '2025-04-29 10:07:35', '0659500307'),
(6, 'hamouche', 'meriem', 'hamouchemeriemm@gmail.com', '$2y$10$0NTG5Q4qdQBzvOmcb7QVaOpTbDlqgmJIgBt3JlCMsqf1JG.KKWfCe', 'BEJAIA', '2025-06-02 19:00:33', '0523784893');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `IdCommande` int NOT NULL AUTO_INCREMENT,
  `IdClient` int NOT NULL,
  `TotalPrix` decimal(10,3) NOT NULL,
  `Status` enum('en attente','expe?die?','livre?','annule?') DEFAULT 'en attente',
  `DateCommande` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdCommande`),
  KEY `fk_commande_client` (`IdClient`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`IdCommande`, `IdClient`, `TotalPrix`, `Status`, `DateCommande`) VALUES
(1, 6, '626000.000', '', '2025-06-03 21:21:01'),
(2, 4, '133000.000', '', '2025-06-03 21:21:48'),
(3, 5, '456000.000', 'expe?die?', '2025-06-03 21:22:47'),
(4, 4, '622999.000', '', '2025-06-03 22:18:50'),
(5, 4, '773999.000', '', '2025-06-03 23:21:08'),
(6, 4, '471000.000', '', '2025-06-03 23:42:28'),
(7, 4, '145999.000', '', '2025-06-03 23:47:50'),
(8, 4, '25500.000', 'livre?', '2025-06-03 23:50:52');

-- --------------------------------------------------------

--
-- Structure de la table `couleur`
--

DROP TABLE IF EXISTS `couleur`;
CREATE TABLE IF NOT EXISTS `couleur` (
  `IdCouleur` int NOT NULL AUTO_INCREMENT,
  `NomCouleur` varchar(55) DEFAULT NULL,
  `ValHex` varchar(7) NOT NULL,
  PRIMARY KEY (`IdCouleur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `couleurproduit`
--

DROP TABLE IF EXISTS `couleurproduit`;
CREATE TABLE IF NOT EXISTS `couleurproduit` (
  `IdCoul` int NOT NULL,
  `IdProd` int NOT NULL,
  `Stock` int NOT NULL,
  PRIMARY KEY (`IdCoul`,`IdProd`),
  KEY `IdProd` (`IdProd`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

DROP TABLE IF EXISTS `favoris`;
CREATE TABLE IF NOT EXISTS `favoris` (
  `IdFavori` int NOT NULL AUTO_INCREMENT,
  `IdClient` int NOT NULL,
  `IdProduit` int NOT NULL,
  `DateAjout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdFavori`),
  UNIQUE KEY `unique_favorite` (`IdClient`,`IdProduit`),
  KEY `IdClient` (`IdClient`),
  KEY `IdProduit` (`IdProduit`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `favoris`
--

INSERT INTO `favoris` (`IdFavori`, `IdClient`, `IdProduit`, `DateAjout`) VALUES
(12, 4, 1, '2025-06-02 18:11:39'),
(11, 4, 3, '2025-06-02 18:11:21'),
(13, 5, 3, '2025-06-02 22:05:30'),
(14, 5, 2, '2025-06-02 22:07:47'),
(15, 7, 36, '2025-06-03 17:34:52'),
(16, 6, 38, '2025-06-03 19:56:40'),
(20, 4, 36, '2025-06-03 22:29:57'),
(21, 4, 30, '2025-06-03 22:29:59'),
(22, 4, 11, '2025-06-03 23:36:02'),
(23, 4, 23, '2025-06-03 23:37:00');

-- --------------------------------------------------------

--
-- Structure de la table `imageprod`
--

DROP TABLE IF EXISTS `imageprod`;
CREATE TABLE IF NOT EXISTS `imageprod` (
  `IdImage` int NOT NULL AUTO_INCREMENT,
  `URL` text NOT NULL,
  `IdProduit` int NOT NULL,
  PRIMARY KEY (`IdImage`),
  KEY `fk_imageprod_produit` (`IdProduit`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `imageprod`
--

INSERT INTO `imageprod` (`IdImage`, `URL`, `IdProduit`) VALUES
(1, 'images/lits2places.png', 1),
(4, 'images/1748939824_2fa5bee5-d0a1-468c-a884-e3d43af701a5.jpg', 5),
(10, 'images/1748940264_téléchargement (9).jpg', 11),
(11, 'images/1748940322_Sophisticated Natural Wood Wardrobe Armoire with 3 Clothing Rods & Drawers Included - Armoire with Top Cabinet 55_L x 24_W x 102_H Armoires & Wardrobe Closets.jpg', 12),
(6, 'images/1748939929_Open Concept_ Wardrobe Designs for Display.jpg', 7),
(8, 'images/1748940100_téléchargement (10).jpg', 9),
(9, 'images/1748940145_téléchargement (8).jpg', 10),
(12, 'images/1748940476_téléchargement (6).jpg', 13),
(13, 'images/1748940545_téléchargement (5).jpg', 14),
(15, 'images/1748940691_téléchargement (7).jpg', 16),
(16, 'images/1748940740_Bellvue Sofa _ Lexington Home Brands.jpg', 17),
(17, 'images/1748940790_Tok&stok.jpg', 18),
(18, 'images/1748940871_Cox and cox.jpg', 19),
(19, 'images/1748940984_téléchargement (3).jpg', 20),
(20, 'images/1748941040_téléchargement (2).jpg', 21),
(21, 'images/1748941357_Chaise rembourrée Holy tissu beige - Pols Potten.jpg', 23),
(22, 'images/1748941416_Lit gigogne 90x200 ALBI.jpg', 24),
(23, 'images/1748941466_30 Modern Bedroom Ideas for Stylish Homes – Unwind in Elegance.jpg', 25),
(24, 'images/1748941526_Napa 4-Piece Platform Bedroom Set With Floating Nightstand - Bed Bath & Beyond - 40390720.jpg', 26),
(25, 'images/1748941578_Short Round Coffee Table Wood End Side Table Living Room Nesting Tables with X Base Leisure Wooden Nightstands Table Accent Sofa Table for Home Office Furniture Decor.jpg', 27),
(27, 'images/1748941841_Prêts pour ton déménagement.jpg', 29),
(28, 'images/1748941920_Table Beauchamp - 4 places (40 x 56).jpg', 30),
(29, 'images/1748941996_téléchargement.jpg', 31),
(30, 'images/1748942096_15 Bonnes Idées Déco Pour Aménager un Petit Salon.jpg', 32),
(31, 'images/1748942152_Table Basse L86,8 Cm - Memo.jpg', 33),
(32, 'images/1748942213_11 Ideal Oval Dining Table Ideas to Complete Your Decor Effortlessly.jpg', 34),
(33, 'images/1748942316_Table à manger ronde HARRY.jpg', 35),
(34, 'images/1748942763_table.jpeg', 36),
(35, 'images/1748942803_armoire.jpeg', 37),
(36, 'images/1748942874_lit.jpeg', 38),
(37, 'images/1748942919_chaise.jpeg', 39),
(38, 'images/1748942957_canape.jpeg', 40);

-- --------------------------------------------------------

--
-- Structure de la table `livraison`
--

DROP TABLE IF EXISTS `livraison`;
CREATE TABLE IF NOT EXISTS `livraison` (
  `IdLivraison` int NOT NULL AUTO_INCREMENT,
  `Adresse` varchar(255) NOT NULL,
  `DateLivraison` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `StatutLivraison` enum('En route','livre','En attente') NOT NULL,
  `Frais` decimal(10,3) DEFAULT NULL,
  `IdComm` int NOT NULL,
  PRIMARY KEY (`IdLivraison`),
  KEY `IdComm` (`IdComm`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `livraison`
--

INSERT INTO `livraison` (`IdLivraison`, `Adresse`, `DateLivraison`, `StatutLivraison`, `Frais`, `IdComm`) VALUES
(1, 'BEJAIA', '2025-05-07 08:09:12', 'En attente', '1000.000', 1),
(2, 'BEJAIA', '2025-05-07 08:18:05', 'En attente', '1000.000', 2),
(3, 'BEJAIA', '2025-05-29 22:29:28', 'En attente', '1000.000', 3),
(4, 'BEJAIA', '2025-06-02 17:53:55', 'En attente', '1000.000', 4),
(5, 'BEJAIA', '2025-06-02 18:44:42', 'En attente', '1000.000', 6),
(6, 'BEJAIA', '2025-06-03 09:46:27', 'En attente', '1000.000', 7);

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

DROP TABLE IF EXISTS `paiement`;
CREATE TABLE IF NOT EXISTS `paiement` (
  `IdPaiement` int NOT NULL AUTO_INCREMENT,
  `TotalPrixF` decimal(10,3) NOT NULL,
  `MethodePaiement` enum('Carte','Cash','Virement') NOT NULL,
  `StatusP` enum('Effectué','En attente','Échoué') DEFAULT NULL,
  `DatePaiment` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Idclt` int NOT NULL,
  `IdCom` int NOT NULL,
  PRIMARY KEY (`IdPaiement`),
  KEY `Idclt` (`Idclt`),
  KEY `IdCom` (`IdCom`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`IdPaiement`, `TotalPrixF`, `MethodePaiement`, `StatusP`, `DatePaiment`, `Idclt`, `IdCom`) VALUES
(1, '3917855.000', 'Cash', 'En attente', '2025-05-07 08:09:12', 4, 1),
(2, '3833932.000', 'Cash', 'En attente', '2025-05-07 08:18:05', 4, 2),
(3, '9999999.999', 'Cash', 'En attente', '2025-05-29 22:29:28', 4, 3),
(4, '1481068.000', 'Cash', 'En attente', '2025-06-02 17:53:55', 4, 4),
(5, '4085701.000', 'Cash', 'En attente', '2025-06-02 18:44:42', 4, 6),
(6, '1191000.000', 'Cash', 'En attente', '2025-06-03 09:46:27', 6, 7);

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

DROP TABLE IF EXISTS `panier`;
CREATE TABLE IF NOT EXISTS `panier` (
  `IdProd` int NOT NULL,
  `IdCom` int NOT NULL,
  `Qtt` int NOT NULL,
  `DatePanier` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdProd`,`IdCom`),
  KEY `IdCom` (`IdCom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `panier`
--

INSERT INTO `panier` (`IdProd`, `IdCom`, `Qtt`, `DatePanier`) VALUES
(17, 1, 1, '2025-06-03 21:21:01'),
(40, 1, 1, '2025-06-03 21:21:01'),
(33, 2, 1, '2025-06-03 21:21:48'),
(10, 3, 1, '2025-06-03 21:22:47'),
(35, 4, 1, '2025-06-03 22:18:50'),
(36, 5, 1, '2025-06-03 23:21:08'),
(30, 5, 1, '2025-06-03 23:21:08'),
(11, 6, 1, '2025-06-03 23:42:28'),
(5, 6, 1, '2025-06-03 23:42:28'),
(35, 7, 1, '2025-06-03 23:47:50'),
(23, 8, 1, '2025-06-03 23:50:52'),
(19, 9, 1, '2025-06-03 20:16:01'),
(21, 9, 1, '2025-06-03 20:16:01'),
(40, 9, 1, '2025-06-03 20:16:01'),
(39, 9, 1, '2025-06-03 20:16:01'),
(21, 10, 1, '2025-06-03 20:16:31'),
(19, 11, 1, '2025-06-03 20:20:12'),
(31, 12, 1, '2025-06-03 20:21:56'),
(39, 13, 1, '2025-06-03 20:25:16'),
(40, 13, 1, '2025-06-03 20:25:16'),
(38, 13, 1, '2025-06-03 20:25:16'),
(40, 14, 1, '2025-06-03 20:38:13'),
(35, 15, 1, '2025-06-03 20:41:59'),
(27, 2, 1, '2025-06-03 21:21:48'),
(14, 3, 1, '2025-06-03 21:22:47'),
(34, 4, 1, '2025-06-03 22:18:50'),
(33, 4, 1, '2025-06-03 22:18:50'),
(39, 4, 1, '2025-06-03 22:18:50'),
(24, 4, 1, '2025-06-03 22:18:50'),
(35, 5, 1, '2025-06-03 23:21:08'),
(38, 5, 1, '2025-06-03 23:21:08');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

DROP TABLE IF EXISTS `produit`;
CREATE TABLE IF NOT EXISTS `produit` (
  `IdProduit` int NOT NULL AUTO_INCREMENT,
  `NomProduit` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Prix` decimal(10,3) NOT NULL,
  `Stock` int NOT NULL,
  `DateAjout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `IdCat` int DEFAULT NULL,
  PRIMARY KEY (`IdProduit`),
  KEY `cat` (`IdCat`)
) ;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`IdProduit`, `NomProduit`, `Description`, `Prix`, `Stock`, `DateAjout`, `IdCat`) VALUES
(5, 'Armoire Coulissante Milano', 'Armoire moderne à portes coulissantes en bois stratifié effet chêne avec miroir intégré. Finition élégante avec cadres noirs. Idéale pour les chambres contemporaines. Dimensions : 200cm x 60cm x 220cm', '185000.000', 9, '2025-06-03 08:37:04', 7),
(11, 'Armoire Murale Scandinave', 'Armoire murale moderne en bois clair avec étagères ouvertes latérales et tiroirs intégrés. Design scandinave épuré, optimisation de l\'espace. Dimensions : 280cm x 45cm x 220cm', '285000.000', 4, '2025-06-03 08:44:24', 7),
(7, 'Armoire Classique Blanche', 'Armoire traditionnelle à 2 portes battantes avec 2 tiroirs inférieurs. Finition laquée blanche mate, poignées métalliques modernes. Idéale pour tous types de décoration. Dimensions : 100cm x 55cm x 200cm', '95000.000', 11, '2025-06-03 08:38:49', 7),
(9, 'Armoire Coulissante Harmony', 'Armoire moderne en bois naturel avec miroir central et portes coulissantes. Finition chêne naturel, design minimaliste et élégant. Parfaite pour les espaces modernes. Dimensions : 240cm x 60cm x 220cm', '225000.000', 6, '2025-06-03 08:41:40', 7),
(10, 'Canapé Moderne Comfort Plus', 'Canapé 3 places en tissu beige chiné avec coussins décoratifs. Structure en bois massif, pieds métalliques noirs. Assise moelleuse et confortable, style contemporain. Dimensions : 280cm x 95cm x 75cm', '165000.000', 8, '2025-06-03 08:42:25', 8),
(12, 'Armoire Modulaire Executive', 'Grande armoire modulaire en bois avec combinaison d\'étagères ouvertes, tiroirs et penderies. Design fonctionnel avec éclairage intégré. Idéale pour dressing. Dimensions : 300cm x 60cm x 240cm', '450000.000', 5, '2025-06-03 08:45:22', 7),
(13, 'Canapé d\'Angle Luxe Modulaire', 'Canapé d\'angle moderne avec méridienne en tissu beige chiné. Coussins décoratifs inclus (noir et beige). Structure robuste, assise profonde et confortable. Parfait pour les grands salons. Dimensions : 320cm x 180cm x 85cm', '285000.000', 6, '2025-06-03 08:47:56', 8),
(14, 'Canapé Minimaliste Pure', 'Canapé 3 places au design épuré en tissu beige clair. Lignes droites et modernes, pieds invisibles. Idéal pour les intérieurs contemporains. Dimensions : 220cm x 90cm x 80cm', '145000.000', 2, '2025-06-03 08:49:05', 8),
(16, 'Canapé Prestige Salon', 'Canapé 3 places haut de gamme en tissu beige premium. Design sophistiqué avec finitions soignées. Assise ferme et durable, parfait pour les salons élégants. Dimensions : 240cm x 100cm x 75cm', '195000.000', 3, '2025-06-03 08:51:31', 8),
(17, 'Canapé Bellvue Luxe', 'Grand canapé 3 places en tissu beige avec nombreux coussins décoratifs à motifs géométriques. Design américain confortable, parfait pour la détente familiale. Dimensions : 260cm x 105cm x 85cm', '225000.000', 0, '2025-06-03 08:52:20', 8),
(18, 'Canapé Compact Gris', 'Canapé 2 places compact en tissu gris chiné. Design simple et fonctionnel, idéal pour les petits espaces. Pieds métalliques discrets. Dimensions : 160cm x 85cm x 80cm', '95000.000', 9, '2025-06-03 08:53:10', 8),
(19, 'Chaise Moderne Élégance', 'Chaise de salle à manger rembourrée en tissu beige avec dossier haut. Pieds métalliques noirs élégants. Confort optimal pour les repas prolongés. Dimensions : 45cm x 55cm x 95cm', '18500.000', 12, '2025-06-03 08:54:31', 9),
(20, 'Chaise Cosy Beige', 'Chaise rembourrée tout en beige avec pieds assortis. Design enveloppant et confortable, parfaite pour salle à manger ou bureau. Finition velours doux. Dimensions : 50cm x 60cm x 85cm', '22000.000', 19, '2025-06-03 08:56:24', 9),
(21, 'Fauteuil Pivotant Rose', 'Fauteuil de bureau ou salon pivotant en tissu rose saumon. Base métallique noire avec mécanisme de rotation 360°. Design moderne et original. Dimensions : 60cm x 65cm x 85cm', '35000.000', 8, '2025-06-03 08:57:20', 9),
(23, 'Chaise Holy Design', 'Chaise rembourrée en tissu beige avec découpe décorative dans le dossier. Pieds entièrement rembourrés assortis. Design original et confortable, parfaite pour salle à manger moderne. Dimensions : 48cm x 55cm x 85cm', '24500.000', 13, '2025-06-03 09:02:37', 9),
(24, 'Lit Gigogne Albi', 'Lit simple 90x200cm avec lit gigogne coulissant et rangement intégré. Finition laquée blanche, parfait pour chambres d\'enfants ou d\'amis. Matelas non inclus. Dimensions : 200cm x 95cm x 85cm', '85000.000', 6, '2025-06-03 09:03:36', 6),
(25, 'Lit Moderne Elegance', 'Lit double avec tête de lit rembourrée en tissu beige. Design contemporain luxueux, structure robuste. Parfait pour chambres modernes. Matelas non inclus. Dimensions : 160cm x 200cm x 110cm', '125000.000', 9, '2025-06-03 09:04:26', 6),
(26, 'Lit Plateforme Napa', 'Ensemble lit plateforme en bois clair avec 2 tables de chevet flottantes intégrées. Style scandinave minimaliste, optimisation de l\'espace. Matelas non inclus. Dimensions : 160cm x 200cm x 40cm', '145000.000', 10, '2025-06-03 09:05:26', 6),
(27, 'Table Basse Ronde Scandinave', 'Table basse ronde en MDF blanc avec pieds en bois naturel en forme de X. Design nordique moderne, parfaite pour salon contemporain. Très stable et élégante. Dimensions : Ø100cm x 45cm', '45000.000', 6, '2025-06-03 09:06:18', 10),
(29, 'Ensemble Table Moderne Bicolore', 'able rectangulaire 6 places en MDF blanc avec bande centrale effet bois. Inclut 6 chaises rembourrées grises avec pieds noirs. Design contemporain élégant. Dimensions table : 180cm x 90cm x 75cm (ensemble complet)', '195000.000', 7, '2025-06-03 09:10:41', 10),
(30, 'Ensemble Table Beauchamp', 'Table rectangulaire 6 places en bois massif teinté noyer avec 6 chaises blanches aux pieds en bois. Style scandinave raffiné, parfait pour salle à manger moderne. Dimensions table : 200cm x 100cm x 75cm (ensemble complet)', '225000.000', 5, '2025-06-03 09:12:00', 10),
(31, 'Ensemble Table Ronde Marbre', 'Table ronde 6 places en marbre beige veiné avec 6 chaises rembourrées beiges aux pieds dorés. Style luxueux et élégant, parfait pour repas familiaux. Dimensions table : 140cm x 75cm (ensemble complet)', '285000.000', 0, '2025-06-03 09:13:16', 10),
(32, 'Table Basse Fonctionnelle Ronde', 'Table basse ronde en bois naturel avec rangement central ouvert et plateau amovible. Design multifonctionnel moderne, parfaite pour salon contemporain. Dimensions : 80cm x 40cm', '55000.000', 9, '2025-06-03 09:14:56', 10),
(33, 'Table Basse Memo Blanche', 'Table basse rectangulaire minimaliste en MDF blanc avec rangement latéral intégré. Design épuré et fonctionnel, idéale pour salons modernes. Dimensions : 87cm x 50cm x 40cm', '42000.000', 6, '2025-06-03 09:15:52', 10),
(34, 'Ensemble Table Ronde Marbre', 'Table ronde 6 places en marbre beige veiné avec 6 chaises rembourrées beiges aux pieds dorés. Style luxueux et élégant, parfait pour repas familiaux. Dimensions table : 140cm x 75cm', '285000.000', 4, '2025-06-03 09:16:53', 10),
(35, 'Ensemble Table Harry Ronde', 'Table ronde 4 places blanche avec pieds en bois en forme de X et 4 chaises beiges rembourrées. Style scandinave moderne, parfait pour cuisine ou salle à manger. Dimensions table : 120cm x 75cm', '144999.000', 4, '2025-06-03 09:18:36', 10),
(36, 'Ensemble Table Prestige 8 Places', 'Table rectangulaire 8 places en bois massif teinté noyer avec 8 chaises rembourrées beiges aux pieds assortis. Design contemporain haut de gamme, parfait pour grandes réceptions. Suspension moderne incluse. Dimensions table : 240cm x 110cm x 75cm (ensemble complet avec suspension)', '385000.000', 3, '2025-06-03 09:26:03', 10),
(37, 'Armoire Murale Luxe Modulaire', 'Grande armoire murale sur mesure en finition beige mat avec étagères ouvertes éclairées LED et rangements fermés. Design ultra-moderne avec éclairage intégré. Parfaite pour salon ou dressing. Dimensions : 350cm x 45cm x 250cm', '520000.000', 2, '2025-06-03 09:26:43', 7),
(38, 'Lit Cosy Arrondi', 'Lit double avec tête de lit rembourrée arrondie en tissu beige chiné. Design contemporain cosy avec tables de chevet assorties en bois clair. Matelas non inclus. Dimensions : 160cm x 200cm x 120cm', '18000.000', 2, '2025-06-03 09:27:54', 6),
(39, 'Fauteuil Bergère Scandinave', 'Fauteuil bergère en tissu beige chiné avec dossier haut et accoudoirs généreux. Pieds en bois naturel, style nordique confortable. Parfait pour coin lecture ou salon. Dimensions : 80cm x 85cm x 105cm', '65000.000', 5, '2025-06-03 09:28:39', 9),
(40, 'Canapé Élégance Gris', 'Canapé 3 places en tissu gris clair avec coussins décoratifs géométriques inclus. Pieds en bois foncé, design contemporain raffiné. Assise ferme et confortable. Dimensions : 220cm x 90cm x 85cm', '175000.000', 0, '2025-06-03 09:29:17', 8);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
