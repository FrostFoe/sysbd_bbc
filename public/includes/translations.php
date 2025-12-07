<?php
$translations = [
    "en" => [
        "home" => "Home",
        "saved" => "Saved",
        "weather" => "Weather",
        "dhaka" => "Dhaka",
        "admin_panel" => "Admin Panel",
        "dashboard" => "Dashboard",
        "sign_out" => "Sign Out",
        "sign_in" => "Sign In",
        "register" => "Register",
        "welcome" => "Welcome",
        "menu" => "Menu",
        "search_placeholder" => "What are you looking for?...",
        "newsletter" => "Newsletter",
        "subscribe_newsletter" => "Subscribe to our newsletter for the latest news and analysis first.",
        "your_email" => "Your email address",
        "subscribe" => "Subscribe",
        "subscribed_successfully" => "Subscribed successfully!",
        "copyright" => "© " . date("Y") . " BreachTimes. All rights reserved.",
        "support_work" => "Support Our Work",
        "support_text" => "We depend 100% on donations to stay independent.",
        "link_copied" => "Link copied to clipboard",
    ],
    "bn" => [
        "home" => "হোম",
        "saved" => "সংরক্ষিত",
        "weather" => "আবহাওয়া",
        "dhaka" => "ঢাকা",
        "admin_panel" => "অ্যাডমিন প্যানেল",
        "dashboard" => "ড্যাশবোর্ড",
        "sign_out" => "সাইন আউট",
        "sign_in" => "সাইন ইন",
        "register" => "নিবন্ধন",
        "welcome" => "স্বাগতম",
        "menu" => "মেনু",
        "search_placeholder" => "কি খুঁজতে চান?...",
        "newsletter" => "নিউজলেটার",
        "subscribe_newsletter" => "সবার আগে সর্বশেষ সংবাদ এবং বিশ্লেষণ পেতে আপনার ইমেইল দিয়ে সাবস্ক্রাইব করুন।",
        "your_email" => "আপনার ইমেইল ঠিকানা",
        "subscribe" => "সাবস্ক্রাইব",
        "subscribed_successfully" => "সাবস্ক্রাইব করা হয়েছে!",
        "copyright" => "© " . convert_to_bengali_num(date("Y")) . " ব্রিচটাইমস। সর্বস্বত্ব সংরক্ষিত।",
        "support_work" => "আমাদের সমর্থন করুন",
        "support_text" => "স্বাধীন থাকার জন্য আমরা ১০০% অনুদানের উপর নির্ভরশীল।",
        "link_copied" => "লিঙ্ক ক্লিপবোর্ডে কপি করা হয়েছে",
    ]
];

function t($key, $lang = "bn") {
    global $translations;
    return $translations[$lang][$key] ?? $key;
}
?>