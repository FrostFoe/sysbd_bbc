<?php
require_once "includes/db.php";

$initialData = [
    "sections" => [
        [
            "id" => "vermont",
            "title" => "শীর্ষ সংবাদ",
            "type" => "hero-grid",
            "highlightColor" => "#B80000",
            "associatedCategory" => "খবর",
            "articles" => [
                [
                    "id" => "1",
                    "title" =>
                        "ওয়াশিংটন ডিসি ঘটনার পর যুক্তরাষ্ট্রে সব আশ্রয় প্রার্থনার সিদ্ধান্ত স্থগিত",
                    "summary" =>
                        "প্রেসিডেন্ট ট্রাম্প 'তৃতীয় বিশ্বের দেশগুলো' থেকে অভিবাসন বন্ধ করার ঘোষণা দেওয়ার কয়েক ঘণ্টা পরই এই নির্দেশনা আসে।",
                    "image" =>
                        "https://ichef.bbci.co.uk/news/480/cpsprodpb/43e9/live/a3d64660-cc34-11f0-9fb5-5f3a3703a365.jpg",
                    "timestamp" => "২ ঘণ্টা আগে",
                    "category" => "যুক্তরাষ্ট্র ও কানাডা",
                    "readTime" => "৫ মিনিট",
                    "comments" => [],
                    "content" =>
                        '<p class="font-bold text-xl md:text-2xl leading-relaxed opacity-90 border-l-4 border-bbcRed pl-4">প্রেসিডেন্ট ট্রাম্প \'তৃতীয় বিশ্বের দেশগুলো\' থেকে অভিবাসন বন্ধ করার ঘোষণা দেওয়ার কয়েক ঘণ্টা পরই এই নির্দেশনা আসে।</p><p>লরেম ইপসাম ডলর সিট আমেট, কনসেক্টেচার এডিপিসিং এলিট। সেড ডু আইউসমড টেম্পোর ইনসিডিন্ট ইউট ল্যাবোর এট ডলোর ম্যাগনা আলিকুয়া। এই প্রতিবেদনটি বিশদভাবে বিশ্লেষণ করে যে কীভাবে বর্তমান পরিস্থিতি আমাদের সমাজ ও অর্থনীতির ওপর প্রভাব ফেলছে।</p><h2>নতুন নির্দেশনা</h2><p>ব্রিচটাইমসের অনুসন্ধানে বেরিয়ে এসেছে নতুন সব তথ্য। আমাদের প্রতিনিধিরা সরেজমিনে গিয়ে এই ঘটনার পেছনের কারণগুলো খতিয়ে দেখেছেন। বিস্তারিত তথ্যের জন্য আমাদের সাথেই থাকুন।</p>',
                ],
                [
                    "id" => "2",
                    "title" =>
                        "হংকংয়ের আগুন কীভাবে ছড়িয়েছিল: একটি দৃশ্যমান নির্দেশিকা",
                    "summary" =>
                        "হংকংয়ের সাম্প্রতিক অগ্নিকাণ্ডের ঘটনাটি ছিল ভয়াবহ। কীভাবে এই আগুন এত দ্রুত ছড়িয়ে পড়ল, তার একটি বিস্তারিত বিশ্লেষণ।",
                    "image" =>
                        "https://ichef.bbci.co.uk/ace/standard/480/cpsprodpb/3fa3/live/626a1d70-ccaf-11f0-8c06-f5d460985095.jpg",
                    "timestamp" => "৪ ঘণ্টা আগে",
                    "category" => "এশিয়া",
                    "readTime" => "৩ মিনিট",
                    "comments" => [],
                    "content" =>
                        "<p>হংকংয়ের সাম্প্রতিক অগ্নিকাণ্ডের ঘটনাটি ছিল ভয়াবহ। কীভাবে এই আগুন এত দ্রুত ছড়িয়ে পড়ল, তার একটি বিস্তারিত বিশ্লেষণ নিচে দেওয়া হলো।</p><p>আগুন লাগার প্রধান কারণ ছিল বৈদ্যুতিক শর্ট সার্কিট। বাতাসের তীব্র গতির কারণে আগুন দ্রুত পার্শ্ববর্তী ভবনগুলোতে ছড়িয়ে পড়ে।</p>",
                ],
            ],
        ],
        [
            "id" => "indiana",
            "title" => "সাপ্তাহিক পাঠ",
            "type" => "feature",
            "highlightColor" => "#B80000",
            "associatedCategory" => "ভ্রমণ",
            "articles" => [
                [
                    "id" => "w1",
                    "title" => "ক্যাবল কারে পৌঁছানো এক অপরূপ গাড়ি-মুক্ত গ্রাম",
                    "summary" =>
                        "ইতিহাসজুড়ে এই মধ্যযুগীয় গ্রামটি বাইরের জগত থেকে প্রায় বিচ্ছিন্ন ছিল। কিন্তু এখন বিশ্বের সবচেয়ে খাড়া ক্যাবল কার পর্যটকদের ৪৩০ জন বাসিন্দার এই গ্রামে নিয়ে যায়।",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/1024xn/p0ky88jq.jpg",
                    "category" => "ভ্রমণ",
                    "readTime" => "৭ মিনিট",
                    "comments" => [],
                    "content" =>
                        "<p>সুইজারল্যান্ডের আল্পস পর্বতমালার কোলে অবস্থিত এই গ্রামটি যেন রূপকথার পাতা থেকে উঠে এসেছে। এখানে কোনো গাড়ি চলে না, কেবল ক্যাবল কারই একমাত্র বাহন।</p>",
                ],
                [
                    "id" => "w2",
                    "title" =>
                        "চেরনোবিলের এই কালো ছত্রাক কি রেডিয়েশন খেয়ে ফেলে?",
                    "summary" =>
                        "চেরনোবিলের পারমাণবিক বিপর্যয়স্থলে পাওয়া মোল্ড মনে হচ্ছে রেডিয়েশন খেয়ে বেঁচে আছে। আমরা কি এটি মহাকাশ ভ্রমণকারীদের সুরক্ষায় ব্যবহার করতে পারি?",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/1920xn/p0mk4zj0.jpg",
                    "category" => "ভবিষ্যৎ",
                    "readTime" => "৫ মিনিট",
                    "comments" => [],
                    "content" =>
                        "<p>বিজ্ঞানীরা অবাক হয়ে দেখছেন যে চেরনোবিলের রেডিয়েশন জোনে এক ধরণের কালো ছত্রাক জন্মাচ্ছে যা রেডিয়েশন শোষণ করে শক্তি উৎপাদন করতে পারে।</p>",
                ],
            ],
        ],
        [
            "id" => "montana",
            "title" => "আর্টস ইন মোশন",
            "type" => "feature",
            "style" => "dark",
            "associatedCategory" => "সংস্কৃতি",
            "articles" => [
                [
                    "id" => "aim1",
                    "title" => 'এলিফ শাফাক: \'আমার লেখার ধরন একটু মাতাল\'',
                    "summary" =>
                        "ব্রিটিশ-তুর্কি ঔপন্যাসিক এলিফ শাফাক ব্রিচটাইমসকে জানান, তার লেখা কোনো ধরাবাঁধা পরিকল্পনার চেয়ে বরং অনুভূতির দ্বারা বেশি পরিচালিত হয়।",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/1920xn/p0mk3x8f.jpg",
                    "category" => "স্পনসরড",
                    "readTime" => "৪ মিনিট",
                    "comments" => [],
                    "content" =>
                        "<p>জনপ্রিয় লেখিকা এলিফ শাফাক তার নতুন বই এবং লেখার প্রক্রিয়া নিয়ে খোলামেলা আলোচনা করেছেন। তিনি বলেন, তার গল্পগুলো চরিত্রের আবেগের সাথে সাথে প্রবাহিত হয়।</p>",
                ],
                [
                    "id" => "aim2",
                    "title" => "আধুনিক শিল্পকলার নতুন দিগন্ত",
                    "summary" =>
                        "ডিজিটাল আর্ট কীভাবে প্রচলিত শিল্প মাধ্যমগুলোকে চ্যালেঞ্জ করছে তার একটি গভীর বিশ্লেষণ।",
                    "image" =>
                        "https://placehold.co/600x400/1a1a1a/FFF?text=Digital+Art",
                    "category" => "শিল্পকলা",
                    "readTime" => "৫ মিনিট",
                    "comments" => [],
                    "content" =>
                        "<p>NFT এবং ডিজিটাল ক্যানভাস শিল্পকলার জগতে বিপ্লব ঘটিয়েছে। শিল্পীরা এখন কোডিং এবং অ্যালগরিদম ব্যবহার করে অদ্ভুত সব শিল্পকর্ম তৈরি করছেন।</p>",
                ],
            ],
        ],
        [
            "id" => "iowa",
            "title" => "সপ্তাহের সেরা অডিও",
            "type" => "audio",
            "highlightColor" => "#B80000",
            "associatedCategory" => "অডিও",
            "articles" => [
                [
                    "id" => "a1",
                    "title" => "মিস ইউনিভার্সের আন্তর্জাতিক নাটক",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/480x480/p0mjv56b.jpg",
                    "category" => "দ্য গ্লোবাল স্টোরি",
                    "readTime" => "১০ মিনিট",
                    "comments" => [],
                    "content" => "<p>অডিও বিবরণ...</p>",
                ],
                [
                    "id" => "a2",
                    "title" => "মারিয়া রেসা: তথ্যের কেয়ামত",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/480x480/p0mk6bzd.jpg",
                    "category" => "দ্য ইন্টারভিউ",
                    "readTime" => "১২ মিনিট",
                    "comments" => [],
                    "content" => "<p>অডিও বিবরণ...</p>",
                ],
            ],
        ],
        [
            "id" => "virginia",
            "title" => "আরও খবর",
            "type" => "grid",
            "highlightColor" => "#B80000",
            "associatedCategory" => "খবর",
            "articles" => [
                [
                    "id" => "mn1",
                    "title" =>
                        "যুক্তরাজ্যে টেক্সট স্ক্যামাররা মাসে হাজার হাজার পাউন্ড হাতিয়ে নিচ্ছে",
                    "image" =>
                        "https://ichef.bbci.co.uk/news/480/cpsprodpb/774b/live/5930ff60-cb9e-11f0-8c06-f5d460985095.png",
                    "category" => "প্রযুক্তি",
                    "readTime" => "৩ মিনিট",
                    "comments" => [],
                    "content" =>
                        "<p>প্রতারকরা নতুন নতুন কৌশলে মানুষের মোবাইলে টেক্সট পাঠিয়ে টাকা হাতিয়ে নিচ্ছে। পুলিশ সবাইকে সতর্ক থাকার পরামর্শ দিয়েছে।</p>",
                ],
                [
                    "id" => "mn2",
                    "title" => "এশিয়ার একাধিক দেশে বন্যায় বহু মানুষের মৃত্যু",
                    "image" =>
                        "https://ichef.bbci.co.uk/news/480/cpsprodpb/515d/live/9c90b640-cc68-11f0-9fb5-5f3a3703a365.jpg",
                    "category" => "এশিয়া",
                    "readTime" => "২ মিনিট",
                    "comments" => [],
                    "content" =>
                        "<p>টানা বৃষ্টিতে এশিয়ার বিভিন্ন দেশে বন্যা পরিস্থিতি ভয়াবহ রূপ নিয়েছে। হাজার হাজার মানুষ পানিবন্দী হয়ে পড়েছে।</p>",
                ],
            ],
        ],
        [
            "id" => "texas",
            "title" => "সম্পাদকের পছন্দ",
            "type" => "reel",
            "highlightColor" => "#B80000",
            "associatedCategory" => "ভিডিও",
            "articles" => [
                [
                    "id" => "r1",
                    "title" => "রোমান দালানগুলো হাজার বছর টিকে থাকে কেন?",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/1920xn/p0mk4lwp.jpg",
                    "category" => "ইতিহাস",
                    "isVideo" => true,
                    "readTime" => "ভিডিও",
                    "comments" => [],
                    "content" => "<p>ভিডিও বিবরণ...</p>",
                ],
                [
                    "id" => "r2",
                    "title" => "যে যন্ত্রটি লাখো স্মৃতি ধরে রেখেছে",
                    "image" =>
                        "https://ichef.bbci.co.uk/images/ic/1920xn/p0mjsmkk.jpg",
                    "category" => "সংস্কৃতি",
                    "isVideo" => true,
                    "readTime" => "ভিডিও",
                    "comments" => [],
                    "content" => "<p>ভিডিও বিবরণ...</p>",
                ],
            ],
        ],
        [
            "id" => "wyoming",
            "title" => "সংবাদ সংগ্রহ",
            "type" => "list",
            "associatedCategory" => "খবর",
            "articles" => [
                [
                    "id" => "l1",
                    "title" =>
                        "ওয়াশিংটন ডিসিতে ন্যাশনাল গার্ডের ওপর গুলির ঘটনায় যা জানা যাচ্ছে",
                    "image" =>
                        "https://ichef.bbci.co.uk/news/480/cpsprodpb/473d/live/ef902dc0-cc32-11f0-9fb5-5f3a3703a365.jpg",
                    "category" => "যুক্তরাষ্ট্র ও কানাডা",
                    "readTime" => "৩ মিনিট",
                    "comments" => [],
                    "content" => "<p>বিস্তারিত সংবাদ...</p>",
                ],
                [
                    "id" => "l2",
                    "title" =>
                        'গিনি-বিসাউয়ের অভ্যুত্থানকে \'ধোঁকাবাজি\' বললেন পশ্চিম আফ্রিকার নেতারা',
                    "image" =>
                        "https://ichef.bbci.co.uk/news/480/cpsprodpb/8796/live/89c4f500-cca2-11f0-aa92-07851ff0caaf.jpg",
                    "category" => "আফ্রিকা",
                    "readTime" => "৪ মিনিট",
                    "comments" => [],
                    "content" => "<p>বিস্তারিত সংবাদ...</p>",
                ],
            ],
        ],
    ],
];

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("DROP TABLE IF EXISTS culprit_profiles");
$pdo->exec("DROP TABLE IF EXISTS culprit_timeline");
$pdo->exec("DROP TABLE IF EXISTS culprit_associates");
$pdo->exec("TRUNCATE TABLE sections");
$pdo->exec("TRUNCATE TABLE articles");
$pdo->exec("TRUNCATE TABLE comments");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$sortOrder = 0;
foreach ($initialData["sections"] as $section) {
    $stmt = $pdo->prepare(
        "INSERT INTO sections (id, title, type, highlight_color, associated_category, style, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)",
    );
    $stmt->execute([
        $section["id"],
        $section["title"],
        $section["type"],
        $section["highlightColor"] ?? null,
        $section["associatedCategory"] ?? null,
        $section["style"] ?? null,
        $sortOrder++,
    ]);

    foreach ($section["articles"] as $article) {
        $stmt = $pdo->prepare(
            "INSERT INTO articles (id, section_id, title, summary, image, timestamp, category, read_time, content, is_video) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        );
        $stmt->execute([
            $article["id"],
            $section["id"],
            $article["title"],
            $article["summary"] ?? null,
            $article["image"] ?? null,
            $article["timestamp"] ?? null,
            $article["category"] ?? null,
            $article["readTime"] ?? null,
            $article["content"] ?? null,
            isset($article["isVideo"]) && $article["isVideo"] ? 1 : 0,
        ]);
    }
}

echo "Database seeded successfully!";
?>
