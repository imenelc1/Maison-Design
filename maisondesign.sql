-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 23 avr. 2025 à 11:14
-- Version du serveur : 8.0.31
-- Version de PHP : 8.0.26

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
(1, 'admin.pass@maison-design.com', 'adminpass', '2025-03-03 10:51:13');

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
  `NomClient` varchar(50) NOT NULL,
  `PrenomClient` varchar(50) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `MDP` varchar(50) NOT NULL,
  `Adresse` varchar(255) NOT NULL,
  `DateInscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `NumTel` varchar(10) NOT NULL,
  PRIMARY KEY (`IdClient`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`IdClient`, `NomClient`, `PrenomClient`, `Email`, `MDP`, `Adresse`, `DateInscription`, `NumTel`) VALUES
(2, 'lc', 'imene', 'imenelc18@gmail.com', 'kk', 'BEJAIA', '2025-03-09 08:34:15', '0659500307');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `IdCommande` int NOT NULL AUTO_INCREMENT,
  `TotalPrix` decimal(10,3) NOT NULL,
  `Status` enum('en attente','expe?die?','livre?','annule?') DEFAULT 'en attente',
  `DateCommande` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdCommande`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `IdComm` int DEFAULT NULL,
  PRIMARY KEY (`IdLivraison`),
  KEY `IdComm` (`IdComm`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `Idclt` int DEFAULT NULL,
  `IdCom` int DEFAULT NULL,
  PRIMARY KEY (`IdPaiement`),
  KEY `Idclt` (`Idclt`),
  KEY `IdCom` (`IdCom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
(2, 'canape chic', 'format l', '3832932.000', 6, '2025-04-16 08:36:10', 2),
(3, 'canape mor', 'forme organique', '83923.000', 9, '2025-04-16 08:51:22', 2);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
