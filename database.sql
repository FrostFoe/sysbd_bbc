-- Database Schema for BreachTimes

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`email`, `password`, `role`) VALUES
('admin@breachtimes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

CREATE TABLE IF NOT EXISTS `categories` (
  `id` varchar(50) NOT NULL,
  `title_bn` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sections` (
  `id` varchar(50) NOT NULL,
  `lang` enum('bn','en') NOT NULL DEFAULT 'bn',
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `highlight_color` varchar(20) DEFAULT NULL,
  `associated_category` varchar(50) DEFAULT NULL,
  `style` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`, `lang`),
  KEY `lang` (`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` varchar(50) NOT NULL,
  `lang` enum('bn','en') NOT NULL DEFAULT 'bn',
  `section_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text,
  `image` longtext,
  `timestamp` varchar(100) DEFAULT NULL,   -- FIXED: accepts human-readable text
  `category_id` varchar(50) DEFAULT NULL,
  `read_time` varchar(50) DEFAULT NULL,
  `content` longtext,
  `leaked_documents` longtext DEFAULT NULL,
  `is_video` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`, `lang`),
  KEY `section_id` (`section_id`),
  KEY `lang` (`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` varchar(50) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `time` varchar(100) DEFAULT NULL,     -- FIXED: must accept "২ ঘণ্টা আগে"
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
