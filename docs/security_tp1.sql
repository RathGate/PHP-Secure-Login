-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 24 juin 2024 à 02:26
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

CREATE DATABASE `security_tp1_temp`;
USE `security_tp1_temp`;

-- --------------------------------------------------------

--
-- Structure de la table `connection_attempts`
--

CREATE TABLE `connection_attempts` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `user.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `public_autorisations`
--

CREATE TABLE `public_autorisations` (
  `id` int(11) NOT NULL,
  `webservice.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `temp_users`
--

CREATE TABLE `temp_users` (
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `stretch` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `uuid` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `temp_user_info`
--

CREATE TABLE `temp_user_info` (
  `temp_user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `stretch` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `uuid` varchar(32) NOT NULL DEFAULT uuid()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_autorisations`
--

CREATE TABLE `user_autorisations` (
  `user.id` int(11) NOT NULL,
  `webservice.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_connections`
--

CREATE TABLE `user_connections` (
  `id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `user.id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_info`
--

CREATE TABLE `user_info` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_otp`
--

CREATE TABLE `user_otp` (
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

CREATE TABLE `webservices` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `webservices`
--

INSERT INTO `webservices` (`id`, `name`) VALUES
(1, 'ChangePassword'),
(2, 'DeleteAccount'),
(4, 'SignedIn'),
(3, 'SignIn'),
(5, 'SignOut'),
(6, 'SignUp');

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
-- Index pour la table `temp_user_info`
--
ALTER TABLE `temp_user_info`
  ADD PRIMARY KEY (`temp_user_id`),
  ADD UNIQUE KEY `email` (`email`);

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
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT pour la table `temp_users`
--
ALTER TABLE `temp_users`
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
-- Contraintes pour la table `temp_user_info`
--
ALTER TABLE `temp_user_info`
  ADD CONSTRAINT `temp_user_info_ibfk_1` FOREIGN KEY (`temp_user_id`) REFERENCES `temp_users` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_otp`
--
ALTER TABLE `user_otp`
  ADD CONSTRAINT `user_otp_ibfk_1` FOREIGN KEY (`user.id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
