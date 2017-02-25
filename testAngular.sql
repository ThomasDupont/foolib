-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:8889
-- Généré le :  Sam 25 Février 2017 à 19:30
-- Version du serveur :  5.6.33
-- Version de PHP :  7.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données :  `Angular`
--

-- --------------------------------------------------------

--
-- Structure de la table `nodes`
--

CREATE TABLE `nodes` (
  `node_ID` int(11) NOT NULL,
  `parentNode_ID` int(11) NOT NULL,
  `path` text NOT NULL,
  `record_name` text NOT NULL,
  `langage` tinytext NOT NULL,
  `authUsers` text NOT NULL,
  `lastModif` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `API_key` varchar(32) NOT NULL,
  `roles` varchar(32) NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `nodes`
--
ALTER TABLE `nodes`
  ADD PRIMARY KEY (`node_ID`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UniqLogin` (`login`(30));

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `nodes`
--
ALTER TABLE `nodes`
  MODIFY `node_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
