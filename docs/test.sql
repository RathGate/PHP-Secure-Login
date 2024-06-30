DROP DATABASE IF EXISTS `security_tp1_test`;
CREATE DATABASE IF NOT EXISTS `security_tp1_test`;
USE `security_tp1_test`;

CREATE TABLE `roles` (
	`id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(32) NOT NULL UNIQUE,
    `permission_level` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id`, `name`, `permission_level`) VALUES
(1, 'Admin', 1),
(2, 'User', 2);

CREATE TABLE `users` (
	`id` int NOT NULL AUTO_INCREMENT,
    `uuid` varchar(36) NOT NULL UNIQUE DEFAULT uuid(),
    `email` varchar(255) NOT NULL UNIQUE,
    `role_id` int DEFAULT 2,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_accounts` (
	`id` int NOT NULL AUTO_INCREMENT,
    `user_uuid` varchar(36) NOT NULL UNIQUE,
    `password` varchar(128) NOT NULL,
    `salt` varchar(128) NOT NULL,
    `stretch` int UNSIGNED DEFAULT 0,
    PRIMARY KEY(`id`),
    FOREIGN KEY (`user_uuid`) REFERENCES `users`(`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_accounts_tmp` (
	`id` int NOT NULL AUTO_INCREMENT,
    `user_uuid` varchar(36) NOT NULL UNIQUE,
    `password` varchar(128) NOT NULL,
    `salt` varchar(128) NOT NULL,
    `stretch` int UNSIGNED DEFAULT 0,
    PRIMARY KEY(`id`),
    FOREIGN KEY (`user_uuid`) REFERENCES `users`(`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_connection_attempts` (
	`id` int NOT NULL AUTO_INCREMENT,
    `user_uuid` varchar(36) NOT NULL,
    `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_uuid`) REFERENCES `users`(`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `webservices` (
	`id` int NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(32) NOT NULL UNIQUE,
    `permission_level` int UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`)
);
    
INSERT INTO `webservices` (`id`, `name`, `permission_level`) VALUES
(1, 'ModifyPassword', NULL),
(2, 'SignUp', NULL),
(3, 'VerifyAccount', NULL),
(4, 'SignIn', NULL),
(5, 'SignedIn', NULL),
(6, 'SignOut', NULL),
(7, 'DeleteAccount', NULL),
(8, 'Admin', 1);

CREATE TABLE `user_otp` (
	`id` int NOT NULL AUTO_INCREMENT,
    `otp` varchar(24) NOT NULL,
    `user_uuid` varchar(36) NOT NULL,
	`webservice_id` int NOT NULL,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `expires_at` datetime NOT NULL,
    `max_uses` int UNSIGNED NOT NULL DEFAULT 5,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_uuid`) REFERENCES `users`(`uuid`) ON DELETE CASCADE,
    FOREIGN KEY (`webservice_id`) REFERENCES `webservices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_sessions` (
	`id` int NOT NULL AUTO_INCREMENT,
    `user_uuid` VARCHAR(36) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
	`created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `expires_at` datetime NOT NULL,
    `validity` int NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_uuid`) REFERENCES `users`(`uuid`) ON DELETE CASCADE
);


