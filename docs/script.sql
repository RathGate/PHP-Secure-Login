-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 09 juin 2024 à 00:11
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `security_tp1`
--

DROP DATABASE IF EXISTS `security_tp1`;
CREATE DATABASE IF NOT EXISTS `security_tp1`;
USE `security_tp1`;

-- --------------------------------------------------------

--
-- Structure de la table `connection_attempts`
--

DROP TABLE IF EXISTS `connection_attempts`;
CREATE TABLE IF NOT EXISTS `connection_attempts` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `user.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `public_autorisations`
--
DROP TABLE IF EXISTS `public_autorisations`;
CREATE TABLE IF NOT EXISTS `public_autorisations` (
  `id` int(11) NOT NULL,
  `webservice.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `temp_users`
--
DROP TABLE IF EXISTS `temp_users`;
CREATE TABLE IF NOT EXISTS `temp_users` (
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `stretch` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` ( 
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `stretch` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_autorisations`
--
DROP TABLE IF EXISTS `user_autorisations`;
CREATE TABLE IF NOT EXISTS `user_autorisations` (
  `user.id` int(11) NOT NULL,
  `webservice.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_connections`
--

DROP TABLE IF EXISTS `user_connections`;
CREATE TABLE IF NOT EXISTS `user_connections` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `user.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
CREATE TABLE IF NOT EXISTS `user_info` (
  `id` int(11) NOT NULL,
  `user.id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_otp`
--

DROP TABLE IF EXISTS `user_otp`;
CREATE TABLE IF NOT EXISTS `user_otp` (
  `id` int(11) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `validity` int(10) UNSIGNED NOT NULL,
  `user.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `webservices`
--

DROP TABLE IF EXISTS `webservices`;
CREATE TABLE IF NOT EXISTS `webservices` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `connection_attempts`
--
ALTER TABLE `connection_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user.id` (`user.id`);

--
-- Index pour la table `public_autorisations`
--
ALTER TABLE `public_autorisations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `webservice.id` (`webservice.id`);

--
-- Index pour la table `temp_users`
--
ALTER TABLE `temp_users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user_autorisations`
--
ALTER TABLE `user_autorisations`
  ADD PRIMARY KEY (`user.id`,`webservice.id`),
  ADD KEY `webservice.id` (`webservice.id`);

--
-- Index pour la table `user_connections`
--
ALTER TABLE `user_connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user.id` (`user.id`);

--
-- Index pour la table `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user.id` (`user.id`);

--
-- Index pour la table `user_otp`
--
ALTER TABLE `user_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user.id` (`user.id`);

--
-- Index pour la table `webservices`
--
ALTER TABLE `webservices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `connection_attempts`
--
ALTER TABLE `connection_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `public_autorisations`
--
ALTER TABLE `public_autorisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_connections`
--
ALTER TABLE `user_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_info`
--
ALTER TABLE `user_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_otp`
--
ALTER TABLE `user_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `webservices`
--
ALTER TABLE `webservices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `connection_attempts`
--
ALTER TABLE `connection_attempts`
  ADD CONSTRAINT `connection_attempts_ibfk_1` FOREIGN KEY (`user.id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `public_autorisations`
--
ALTER TABLE `public_autorisations`
  ADD CONSTRAINT `public_autorisations_ibfk_1` FOREIGN KEY (`webservice.id`) REFERENCES `webservices` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_autorisations`
--
ALTER TABLE `user_autorisations`
  ADD CONSTRAINT `user_autorisations_ibfk_1` FOREIGN KEY (`user.id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_autorisations_ibfk_2` FOREIGN KEY (`webservice.id`) REFERENCES `webservices` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_connections`
--
ALTER TABLE `user_connections`
  ADD CONSTRAINT `user_connections_ibfk_1` FOREIGN KEY (`user.id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`user.id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `user_otp`
--
ALTER TABLE `user_otp`
  ADD CONSTRAINT `user_otp_ibfk_1` FOREIGN KEY (`user.id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
