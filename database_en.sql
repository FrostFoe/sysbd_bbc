-- Database Schema for BreachTimes

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `breachtimes`
--

-- --------------------------------------------------------

--

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections_en` (
  `id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `highlight_color` varchar(20) DEFAULT NULL,
  `associated_category` varchar(50) DEFAULT NULL,
  `style` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE IF NOT EXISTS `articles_en` (
  `id` varchar(50) NOT NULL,
  `section_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text,
  `image` longtext, -- Using longtext for base64 images if needed, though URL is better
  `timestamp` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `read_time` varchar(50) DEFAULT NULL,
  `content` longtext,
  `is_video` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `section_id` (`section_id`),
  CONSTRAINT `fk_articles_section_en` FOREIGN KEY (`section_id`) REFERENCES `sections_en` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments_en` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` varchar(50) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `time` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `fk_comments_article_en` FOREIGN KEY (`article_id`) REFERENCES `articles_en` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
