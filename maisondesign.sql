-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 30 mai 2025 à 20:18
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
(1, 'lit'),
(2, 'canapé'),
(3, 'armoire'),
(4, 'chaise'),
(5, 'table');

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
(4, 'lcc', 'imene', 'imenelc18@gmail.com', '$2y$10$6fo7kwYA99RJkwn2cmOgjekBrhPWf3cRySUtTAUiLShk1Ru7IFa.m', 'BEJAIA', '2025-04-29 10:07:35', '0659500307');

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`IdCommande`, `IdClient`, `TotalPrix`, `Status`, `DateCommande`) VALUES
(1, 4, '3917855.000', 'en attente', '2025-05-07 08:09:12'),
(2, 4, '3833932.000', 'en attente', '2025-05-07 08:18:05'),
(3, 4, '9999999.999', 'en attente', '2025-05-29 22:29:28');

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `imageprod`
--

INSERT INTO `imageprod` (`IdImage`, `URL`, `IdProduit`) VALUES
(1, 'images/lits2places.png', 1),
(2, 'images/canape2', 2),
(3, 'images/formorganiquebeige.jpg', 3);

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `livraison`
--

INSERT INTO `livraison` (`IdLivraison`, `Adresse`, `DateLivraison`, `StatutLivraison`, `Frais`, `IdComm`) VALUES
(1, 'BEJAIA', '2025-05-07 08:09:12', 'En attente', '1000.000', 1),
(2, 'BEJAIA', '2025-05-07 08:18:05', 'En attente', '1000.000', 2),
(3, 'BEJAIA', '2025-05-29 22:29:28', 'En attente', '1000.000', 3);

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`IdPaiement`, `TotalPrixF`, `MethodePaiement`, `StatusP`, `DatePaiment`, `Idclt`, `IdCom`) VALUES
(1, '3917855.000', 'Cash', 'En attente', '2025-05-07 08:09:12', 4, 1),
(2, '3833932.000', 'Cash', 'En attente', '2025-05-07 08:18:05', 4, 2),
(3, '9999999.999', 'Cash', 'En attente', '2025-05-29 22:29:28', 4, 3);

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
(3, 1, 1, '2025-05-07 08:09:12'),
(2, 1, 1, '2025-05-07 08:09:12'),
(2, 2, 1, '2025-05-07 08:18:05'),
(2, 3, 3, '2025-05-29 22:29:28');

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`IdProduit`, `NomProduit`, `Description`, `Prix`, `Stock`, `DateAjout`, `IdCat`) VALUES
(1, 'lit chic', 'lit deux places ', '123339.000', 6, '2025-04-15 12:53:33', 1),
(2, 'canape chic', 'format l', '3832932.000', 1, '2025-04-16 08:36:10', 2),
(3, 'canape mor', 'forme organique', '83923.000', 8, '2025-04-16 08:51:22', 2);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
