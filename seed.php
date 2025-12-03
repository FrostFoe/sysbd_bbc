<?php
require_once "includes/db.php";

// Consolidated data structure: single array with BN/EN for each field
$initialData = [
    "sections" => [
        [
            "id" => "vermont",
            "title_bn" => "শীর্ষ সংবাদ",
            "title_en" => "Top Stories",
            "type" => "hero-grid",
            "highlightColor" => "#B80000",
            "associatedCategory_bn" => "খবর",
            "associatedCategory_en" => "News",
            "articles" => [
                [
                    "id" => "1",
                    "title_bn" =>
                        "ওয়াশিংটন ডিসি ঘটনার পর যুক্তরাষ্ট্রে সব আশ্রয় প্রার্থনার সিদ্ধান্ত স্থগিত",
                    "title_en" =>
                        "US suspends all asylum applications after Washington DC incident",
                    "summary_bn" =>
                        "প্রেসিডেন্ট ট্রাম্প 'তৃতীয় বিশ্বের দেশগুলো' থেকে অভিবাসন বন্ধ করার ঘোষণা দেওয়ার কয়েক ঘণ্টা পরই এই নির্দেশনা আসে।",
                    "summary_en" =>
                        "The directive comes hours after President Trump announced a halt to immigration from 'third world countries'.",
                    "image" =>
                        "https://ichef.bbci.co.uk/news/480/cpsprodpb/43e9/live/a3d64660-cc34-11f0-9fb5-5f3a3703a365.jpg",
                    "timestamp_bn" => "२ ঘণ্টা আগে",
                    "timestamp_en" => "2 hours ago",
                    "category_bn" => "যুক্তরাষ্ট্র ও কানাডা",
                    "category_en" => "US & Canada",
                    "readTime_bn" => "५ মিনিট",
                    "readTime_en" => "5 min",
                    "content_bn" =>
                        '<p class="font-bold text-xl md:text-2xl leading-relaxed opacity-90 border-l-4 border-bbcRed pl-4">প্রেসিডেন্ট ট্রাম্প \'তৃতীয় বিশ্বের দেশগুলো\' থেকে অভিবাসন বন্ধ করার ঘোষণা দেওয়ার কয়েক ঘণ্টা পরই এই নির্দেশনা আসে।</p><p>লরেম ইপসাম ডলর সিট আমেট, কনসেক্টেচার এডিপিসিং এলিট। সেড ডু আইউসমড টেম্পোর ইনসিডিন্ট ইউট ল্যাবোর এট ডলোর ম্যাগনা আলিকুয়া। এই প্রতিবেদনটি বিশদভাবে বিশ্লেষণ করে যে কীভাবে বর্তমান পরিস্থিতি আমাদের সমাজ ও অর্থনীতির ওপর প্রভাব ফেলছে।</p><h2>নতুন নির্দেশনা</h2><p>ব্রিচটাইমসের অনুসন্ধানে বেরিয়ে এসেছে নতুন সব তথ্য। আমাদের প্রতিনিধিরা সরেজমিনে গিয়ে এই ঘটনার পেছনের কারণগুলো খতিয়ে দেখেছেন। বিস্তারিত তথ্যের জন্য আমাদের সাথেই থাকুন।</p>',
                    "content_en" =>
                        '<p class="font-bold text-xl md:text-2xl leading-relaxed opacity-90 border-l-4 border-bbcRed pl-4">The directive comes hours after President Trump announced a halt to immigration from \'third world countries\'.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. This report analyzes in detail how the current situation is affecting our society and economy.</p><h2>New Directive</h2><p>BreachTimes\' investigation has revealed new information. Our correspondents have gone to the scene to investigate the reasons behind this incident. Stay with us for more details.</p>',
                ],
                [
                    "id" => "2",
                    "title_bn" =>
                        "হংকংয়ের আগুন কীভাবে ছড়িয়েছিল: একটি দৃশ্যমান নির্দেশিকা",
                    "title_en" =>
                        "How the Hong Kong fire spread: a visual guide",
                    "summary_bn" =>
                        "হংকংয়ের সাম্প্রতিক অগ্নিকাণ্ডের ঘটনাটি ছিল ভয়াবহ। কীভাবে এই আগুন এত দ্রুত ছড়িয়ে পড়ল, তার একটি বিস্তারিত বিশ্লেষণ।",
                    "summary_en" =>
                        "The recent fire in Hong Kong was devastating. A detailed analysis of how this fire spread so quickly.",
                    "image" =>
                        "https://ichef.bbci.co.uk/ace/standard/480/cpsprodpb/3fa3/live/626a1d70-ccaf-11f0-8c06-f5d460985095.jpg",
                    "timestamp_bn" => "४ ঘণ্টা আগে",
                    "timestamp_en" => "4 hours ago",
                    "category_bn" => "এশিয়া",
                    "category_en" => "Asia",
                    "readTime_bn" => "३ মিনিট",
                    "readTime_en" => "3 min",
                    "content_bn" =>
                        "<p>হংকংয়ের সাম্প্রতিক অগ্নিকাণ্ডের ঘটনাটি ছিল ভয়াবহ। কীভাবে এই আগুন এত দ্রুত ছড়িয়ে পড়ল, তার একটি বিস্তারিত বিশ্লেষণ নিচে দেওয়া হলো।</p><p>আগুন লাগার প্রধান কারণ ছিল বৈদ্যুতিক শর্ট সার্কিট। বাতাসের তীব্র গতির কারণে আগুন দ্রুত পার্শ্ববর্তী ভবনগুলোতে ছড়িয়ে পড়ে।</p>",
                    "content_en" =>
                        "<p>The recent fire in Hong Kong was devastating. A detailed analysis of how this fire spread so quickly is given below.</p><p>The main cause of the fire was an electrical short circuit. Due to the high speed of the wind, the fire spread rapidly to the surrounding buildings.</p>",
                ],
            ],
        ],
        [
            "id" => "indiana",
            "title_bn" => "সাপ্তাহিক পাঠ",
            "title_en" => "Weekly Reading",
            "type" => "feature",
            "highlightColor" => "#B80000",
            "associatedCategory_bn" => "ভ্রমণ",
            "associatedCategory_en" => "Travel",
            "articles" => [
                [
                    "id" => "w1",
                    "title_bn" =>
                        "ক্যাবল কারে পৌঁছানো এক অপরূপ গাড়ি-মুক্ত গ্রাম",
                    "title_en" =>
                        "A beautiful car-free village reached by cable car",
                    "summary_bn" =>
                        "ইতিহাসজুড়ে এই মধ্যযুগীয় গ্রামটি বাইরের জগত থেকে প্রায় বিচ্ছিন্ন ছিল। কিন্তু এখন বিশ্বের সবচেয়ে খাড়া ক্যাবল কার পর্যটকদের ४३० জন বাসিন্দার এই গ্রামে নিয়ে যায়।",
                    "summary_en" =>
                        "Throughout history, this medieval village was almost completely cut off from the outside world. But now the world's steepest cable car takes tourists to this village of 430 inhabitants.",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/1024xn/p0ky88jq.jpg",
                    "category_bn" => "ভ্রমণ",
                    "category_en" => "Travel",
                    "readTime_bn" => "७ मिनिट",
                    "readTime_en" => "7 min",
                    "content_bn" =>
                        "<p>সুইজারল্যান্ডের আল্পস পর্বতমালার কোলে অবস্থিত এই গ্রামটি যেন রূপকথার পাতা থেকে উঠে এসেছে। এখানে কোনো গাড়ি চলে না, কেবল ক্যাবল কারই একমাত্র বাহন।</p>",
                    "content_en" =>
                        "<p>Nestled in the Swiss Alps, this village seems to have come straight out of a fairy tale. No cars run here, only cable cars are the only means of transport.</p>",
                ],
                [
                    "id" => "w2",
                    "title_bn" =>
                        "চেরনোবিলের এই কালো ছত্রাক কি রেডিয়েশন খেয়ে ফেলে?",
                    "title_en" =>
                        "Does this black fungus from Chernobyl eat radiation?",
                    "summary_bn" =>
                        "চেরনোবিলের পারমাণবিক বিপর্যয়স্থলে পাওয়া মোল্ড মনে হচ্ছে রেডিয়েশন খেয়ে বেঁচে আছে। আমরা কি এটি মহাকাশ ভ্রমণকারীদের সুরক্ষায় ব্যবহার করতে পারি?",
                    "summary_en" =>
                        "The mold found at the Chernobyl nuclear disaster site appears to be surviving by eating radiation. Could we use it to protect space travelers?",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/1920xn/p0mk4zj0.jpg",
                    "category_bn" => "ভবিষ্যৎ",
                    "category_en" => "Future",
                    "readTime_bn" => "५ मिनिट",
                    "readTime_en" => "5 min",
                    "content_bn" =>
                        "<p>বিজ্ঞানীরা অবাক হয়ে দেখছেন যে চেরনোবিলের রেডিয়েশন জোনে এক ধরণের কালো ছত্রাক জন্মাচ্ছে যা রেডিয়েশন শোষণ করে শক্তি উৎপাদন করতে পারে।</p>",
                    "content_en" =>
                        "<p>Scientists are surprised to see that a type of black fungus is growing in the radiation zone of Chernobyl that can absorb radiation and produce energy.</p>",
                ],
            ],
        ],
    ],
];

// Categories to seed (same IDs used in database.sql)
$initialCategories = [
    [
        "id" => "news",
        "title_bn" => "খবর",
        "title_en" => "News",
        "color" => "#b80000",
    ],
    [
        "id" => "us-canada",
        "title_bn" => "যুক্তরাষ্ট্র ও কানাডা",
        "title_en" => "US & Canada",
        "color" => "#ff6b35",
    ],
    [
        "id" => "asia",
        "title_bn" => "এশিয়া",
        "title_en" => "Asia",
        "color" => "#00a8e8",
    ],
    [
        "id" => "sport",
        "title_bn" => "খেলা",
        "title_en" => "Sport",
        "color" => "#00c9a7",
    ],
    [
        "id" => "business",
        "title_bn" => "ব্যবসা",
        "title_en" => "Business",
        "color" => "#9c27b0",
    ],
    [
        "id" => "innovation",
        "title_bn" => "উদ্ভাবন",
        "title_en" => "Innovation",
        "color" => "#f57c00",
    ],
    [
        "id" => "culture",
        "title_bn" => "সংস্কৃতি",
        "title_en" => "Culture",
        "color" => "#1976d2",
    ],
    [
        "id" => "arts",
        "title_bn" => "শিল্প",
        "title_en" => "Arts",
        "color" => "#7b1fa2",
    ],
    [
        "id" => "travel",
        "title_bn" => "ভ্রমণ",
        "title_en" => "Travel",
        "color" => "#d32f2f",
    ],
    [
        "id" => "future",
        "title_bn" => "ভবিষ্যৎ",
        "title_en" => "Future",
        "color" => "#4caf50",
    ],
    [
        "id" => "audio",
        "title_bn" => "অডিও",
        "title_en" => "Audio",
        "color" => "#ff9800",
    ],
    [
        "id" => "video",
        "title_bn" => "ভিডিও",
        "title_en" => "Video",
        "color" => "#2196f3",
    ],
];

// Helper for categories
function seedCategories($pdo, $categories)
{
    $pdo->exec("TRUNCATE TABLE categories");
    $stmt = $pdo->prepare(
        "INSERT INTO categories (id, title_bn, title_en, color) VALUES (?, ?, ?, ?)",
    );
    foreach ($categories as $cat) {
        $stmt->execute([
            $cat["id"],
            $cat["title_bn"],
            $cat["title_en"],
            $cat["color"],
        ]);
    }
}

function lookupCategoryId($pdo, $lang, $name)
{
    if (empty($name)) {
        return null;
    }
    if ($lang === "en") {
        $stmt = $pdo->prepare(
            "SELECT id FROM categories WHERE title_en = ? LIMIT 1",
        );
    } else {
        $stmt = $pdo->prepare(
            "SELECT id FROM categories WHERE title_bn = ? LIMIT 1",
        );
    }
    $stmt->execute([$name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row["id"] : null;
}

// Consolidated seed function that handles both BN and EN from single data structure
function seedData($pdo, $data)
{
    $sectionsTable = "sections";
    $articlesTable = "articles";

    // Delete existing data for both languages
    $pdo->exec("DELETE FROM $articlesTable");
    $pdo->exec("DELETE FROM $sectionsTable");

    $sortOrder = 0;
    foreach ($data["sections"] as $section) {
        // Seed BN section
        $stmt = $pdo->prepare(
            "INSERT INTO $sectionsTable (id, lang, title, type, highlight_color, associated_category, style, sort_order) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        );
        $assocCatId_bn = lookupCategoryId(
            $pdo,
            "bn",
            $section["associatedCategory_bn"] ?? null,
        );
        $stmt->execute([
            $section["id"],
            "bn",
            $section["title_bn"],
            $section["type"],
            $section["highlightColor"] ?? null,
            $section["associatedCategory_bn"] ?? null,
            $section["style"] ?? null,
            $sortOrder,
        ]);

        // Seed EN section
        $assocCatId_en = lookupCategoryId(
            $pdo,
            "en",
            $section["associatedCategory_en"] ?? null,
        );
        $stmt->execute([
            $section["id"],
            "en",
            $section["title_en"],
            $section["type"],
            $section["highlightColor"] ?? null,
            $section["associatedCategory_en"] ?? null,
            $section["style"] ?? null,
            $sortOrder,
        ]);

        // Seed articles for both languages
        foreach ($section["articles"] as $article) {
            // BN article
            $articleCategoryId_bn = lookupCategoryId(
                $pdo,
                "bn",
                $article["category_bn"] ?? null,
            );
            $stmt = $pdo->prepare(
                "INSERT INTO $articlesTable (id, lang, section_id, title, summary, image, timestamp, category_id, read_time, content, is_video) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            );
            $stmt->execute([
                $article["id"],
                "bn",
                $section["id"],
                $article["title_bn"],
                $article["summary_bn"] ?? null,
                $article["image"] ?? null,
                $article["timestamp_bn"] ?? null,
                $articleCategoryId_bn,
                $article["readTime_bn"] ?? null,
                $article["content_bn"] ?? null,
                0,
            ]);

            // EN article
            $articleCategoryId_en = lookupCategoryId(
                $pdo,
                "en",
                $article["category_en"] ?? null,
            );
            $stmt->execute([
                $article["id"],
                "en",
                $section["id"],
                $article["title_en"],
                $article["summary_en"] ?? null,
                $article["image"] ?? null,
                $article["timestamp_en"] ?? null,
                $articleCategoryId_en,
                $article["readTime_en"] ?? null,
                $article["content_en"] ?? null,
                0,
            ]);
        }

        $sortOrder++;
    }
}

// Main seed execution
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Drop old separate language tables if they exist
$pdo->exec("DROP TABLE IF EXISTS culprit_profiles");
$pdo->exec("DROP TABLE IF EXISTS culprit_timeline");
$pdo->exec("DROP TABLE IF EXISTS culprit_associates");
$pdo->exec("DROP TABLE IF EXISTS articles_en");
$pdo->exec("DROP TABLE IF EXISTS sections_en");
$pdo->exec("DROP TABLE IF EXISTS comments_en");

// Ensure all required tables exist with correct schema
$pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
  `id` varchar(50) NOT NULL,
  `title_bn` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS `sections` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS `articles` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` varchar(50) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `time` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert default admin user if doesn't exist
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(["admin@breachtimes.com"]);
if (!$stmt->fetch()) {
    $pdo->exec(
        "INSERT INTO users (email, password, role) VALUES ('admin@breachtimes.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')",
    );
}

// Seed categories before articles
seedCategories($pdo, $initialCategories);

// Seed data for both languages from single consolidated data structure
seedData($pdo, $initialData);

// Truncate comments table to start fresh
$pdo->exec("TRUNCATE TABLE comments");

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "✅ Database seeded successfully! One-click fresh start ready for both BN and EN content.";
?>
