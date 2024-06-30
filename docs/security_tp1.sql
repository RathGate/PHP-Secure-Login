-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 30 juin 2024 à 04:14
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

-- --------------------------------------------------------

--
-- Structure de la table `otp_attempts`
--

CREATE TABLE `otp_attempts` (
  `id` int(11) NOT NULL,
  `otp_id` int(11) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `session_tokens`
--

CREATE TABLE `session_tokens` (
  `id` int(11) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `validity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `session_tokens`
--

INSERT INTO `session_tokens` (`id`, `user_uuid`, `token`, `created_at`, `expires_at`, `validity`) VALUES
(6, 'c820f3d1-364d-11ef-9a89-d8bbc121cf88', '9c5a40ec7131bb3e8bc5a49b936dcf8917476621ec9b0c04467bc7e44aa863a8', '2024-06-30 04:11:20', '2024-07-01 04:11:20', 86400);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL DEFAULT uuid(),
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `uuid`, `email`) VALUES
(13, 'bbbd97d8-364d-11ef-9a89-d8bbc121cf88', 'mariannecorbelk@hotmail.fr'),
(14, 'c820f3d1-364d-11ef-9a89-d8bbc121cf88', 'mariannecorbel@hotmail.fr');

-- --------------------------------------------------------

--
-- Structure de la table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `id` int(11) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `stretch` int(10) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_accounts`
--

INSERT INTO `user_accounts` (`id`, `user_uuid`, `password`, `salt`, `stretch`) VALUES
(2, 'c820f3d1-364d-11ef-9a89-d8bbc121cf88', '99e441b0a578d4e19120c7fc08ec823254c512689831aea00737fdf30b697044b4ab1795fd8b49b87992b9c56d49cbf886e9e727327a6294baedc9f9d179ea23', 'LCHk6YLLyMA9mcyGLrw7sJwwm1s8inxWwSCl/zXuRP8yZ5FefHGgqV4F27FN1JTj0yEbL+R4Rdca2KmEeYS//w==', 1000);

-- --------------------------------------------------------

--
-- Structure de la table `user_accounts_tmp`
--

CREATE TABLE `user_accounts_tmp` (
  `id` int(11) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `stretch` int(10) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_accounts_tmp`
--

INSERT INTO `user_accounts_tmp` (`id`, `user_uuid`, `password`, `salt`, `stretch`) VALUES
(9, 'bbbd97d8-364d-11ef-9a89-d8bbc121cf88', 'a3377740f9177d8fe81164c450af86ec76831287c415d0e3ffd66c34fb03ab5b36f4c21a3ef5dc514f46596c00d9e21f5c47cdc8406391f3ce4f364277fc433f', '564AdsrSKPPIumEGsDzzVkF6N9N8z4dykcXG6fN/sw5evrrAFVN5K0vWixX/IrlTZY0FUtyEqjjBfD55F+DnTA==', 1000),
(10, 'c820f3d1-364d-11ef-9a89-d8bbc121cf88', '99e441b0a578d4e19120c7fc08ec823254c512689831aea00737fdf30b697044b4ab1795fd8b49b87992b9c56d49cbf886e9e727327a6294baedc9f9d179ea23', 'LCHk6YLLyMA9mcyGLrw7sJwwm1s8inxWwSCl/zXuRP8yZ5FefHGgqV4F27FN1JTj0yEbL+R4Rdca2KmEeYS//w==', 1000);

-- --------------------------------------------------------

--
-- Structure de la table `user_connection_attempts`
--

CREATE TABLE `user_connection_attempts` (
  `id` int(11) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_connection_attempts`
--

INSERT INTO `user_connection_attempts` (`id`, `user_uuid`, `attempted_at`) VALUES
(1, 'c820f3d1-364d-11ef-9a89-d8bbc121cf88', '2024-06-29 23:31:48');

-- --------------------------------------------------------

--
-- Structure de la table `user_otp`
--

CREATE TABLE `user_otp` (
  `id` int(11) NOT NULL,
  `otp` varchar(64) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `max_uses` int(10) UNSIGNED NOT NULL DEFAULT 10,
  `webservice_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_otp`
--

INSERT INTO `user_otp` (`id`, `otp`, `user_uuid`, `created_at`, `expires_at`, `max_uses`, `webservice_id`) VALUES
(10, '4428a16cf1', 'bbbd97d8-364d-11ef-9a89-d8bbc121cf88', '2024-06-29 21:28:16', '2024-06-29 21:33:16', 10, 2);

-- --------------------------------------------------------

--
-- Structure de la table `webservices`
--

CREATE TABLE `webservices` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `webservices`
--

INSERT INTO `webservices` (`id`, `name`) VALUES
(1, 'ChangePassword'),
(7, 'DeleteAccount'),
(5, 'SignedIn'),
(4, 'SignIn'),
(6, 'SignOut'),
(2, 'SignUp'),
(3, 'VerifyAccount');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `otp_attempts`
--
ALTER TABLE `otp_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otp_id` (`otp_id`);

--
-- Index pour la table `session_tokens`
--
ALTER TABLE `session_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_uuid` (`user_uuid`);

--
-- Index pour la table `user_accounts_tmp`
--
ALTER TABLE `user_accounts_tmp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_uuid` (`user_uuid`);

--
-- Index pour la table `user_connection_attempts`
--
ALTER TABLE `user_connection_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Index pour la table `user_otp`
--
ALTER TABLE `user_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_uuid` (`user_uuid`),
  ADD KEY `webservice_id` (`webservice_id`);

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
-- AUTO_INCREMENT pour la table `otp_attempts`
--
ALTER TABLE `otp_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `session_tokens`
--
ALTER TABLE `session_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `user_accounts_tmp`
--
ALTER TABLE `user_accounts_tmp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user_connection_attempts`
--
ALTER TABLE `user_connection_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `user_otp`
--
ALTER TABLE `user_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `otp_attempts`
--
ALTER TABLE `otp_attempts`
  ADD CONSTRAINT `otp_attempts_ibfk_1` FOREIGN KEY (`otp_id`) REFERENCES `user_otp` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `session_tokens`
--
ALTER TABLE `session_tokens`
  ADD CONSTRAINT `session_tokens_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD CONSTRAINT `user_accounts_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_accounts_tmp`
--
ALTER TABLE `user_accounts_tmp`
  ADD CONSTRAINT `user_accounts_tmp_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_connection_attempts`
--
ALTER TABLE `user_connection_attempts`
  ADD CONSTRAINT `user_connection_attempts_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_otp`
--
ALTER TABLE `user_otp`
  ADD CONSTRAINT `user_otp_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_otp_ibfk_2` FOREIGN KEY (`webservice_id`) REFERENCES `webservices` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
