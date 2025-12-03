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
('admin@breachtimes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password: password

CREATE TABLE IF NOT EXISTS `categories` (
  `id` varchar(50) NOT NULL,
  `title_bn` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `title_bn`, `title_en`, `color`) VALUES
('news', 'খবর', 'News', '#b80000'),
('sport', 'খেলা', 'Sport', '#ff6b35'),
('business', 'ব্যবসা', 'Business', '#00a8e8'),
('innovation', 'উদ্ভাবন', 'Innovation', '#00c9a7'),
('culture', 'সংস্কৃতি', 'Culture', '#9c27b0'),
('arts', 'শিল্প', 'Arts', '#f57c00'),
('travel', 'ভ্রমণ', 'Travel', '#1976d2'),
('audio', 'অডিও', 'Audio', '#7b1fa2'),
('video', 'ভিডিও', 'Video', '#d32f2f');

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
  `timestamp` varchar(100) DEFAULT NULL,
  `category_id` varchar(50) DEFAULT NULL,
  `read_time` varchar(50) DEFAULT NULL,
  `content` longtext,
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
  `time` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


COMMIT;