<?php
require_once 'api_header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(['error' => 'Method not allowed'], 405);
}

$id = $_POST['id'] ?? uniqid(); // Use existing ID or generate new
$title = $_POST['title'] ?? '';
$category = $_POST['category'] ?? 'খবর';
$summary = $_POST['summary'] ?? '';
$content = $_POST['content'] ?? '';
$sectionId = $_POST['sectionId'] ?? 'news';
$image = $_POST['image'] ?? '';

// Handle Image Upload if file is present
// Note: In the JS, the image is read as DataURL and put into the input. 
// But if we want "proper file organization", we should handle file uploads.
// However, the JS `handleImageUpload` puts the base64 string into the text input `image`.
// So `$_POST['image']` will contain the base64 string or URL.
// We can save this directly or decode and save to file. 
// For simplicity and "preserving UI", we'll keep the base64/URL string in DB.

// Check if article exists
$stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ?");
$stmt->execute([$id]);
$exists = $stmt->fetch();

if ($exists) {
    $stmt = $pdo->prepare("UPDATE articles SET title=?, summary=?, image=?, category=?, content=?, section_id=? WHERE id=?");
    $stmt->execute([$title, $summary, $image, $category, $content, $sectionId, $id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO articles (id, section_id, title, summary, image, category, content, timestamp, read_time) VALUES (?, ?, ?, ?, ?, ?, ?, 'সদ্য', '৩ মিনিট')");
    $stmt->execute([$id, $sectionId, $title, $summary, $image, $category, $content]);
}

// Handle Culprit Profile
if (isset($_POST['hasProfile']) && $_POST['hasProfile'] === 'on') {
    $profileName = $_POST['profileName'] ?? '';
    $profileCrime = $_POST['profileCrime'] ?? '';
    $profileStatus = $_POST['profileStatus'] ?? '';
    $profileDesc = $_POST['profileDesc'] ?? '';
    $profileImage = $_POST['profileImage'] ?? '';

    // Check if profile exists
    $stmt = $pdo->prepare("SELECT id FROM culprit_profiles WHERE article_id = ?");
    $stmt->execute([$id]);
    $profile = $stmt->fetch();

    if ($profile) {
        $stmt = $pdo->prepare("UPDATE culprit_profiles SET name=?, crime=?, status=?, description=?, image=? WHERE article_id=?");
        $stmt->execute([$profileName, $profileCrime, $profileStatus, $profileDesc, $profileImage, $id]);
        $profileId = $profile['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO culprit_profiles (article_id, name, crime, status, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $profileName, $profileCrime, $profileStatus, $profileDesc, $profileImage]);
        $profileId = $pdo->lastInsertId();
    }

    // Handle Timeline
    $pdo->prepare("DELETE FROM culprit_timeline WHERE profile_id = ?")->execute([$profileId]);
    if (!empty($_POST['profileTimeline'])) {
        $lines = explode("\n", $_POST['profileTimeline']);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($year, $event) = explode(':', $line, 2);
                $stmt = $pdo->prepare("INSERT INTO culprit_timeline (profile_id, year, event) VALUES (?, ?, ?)");
                $stmt->execute([$profileId, trim($year), trim($event)]);
            }
        }
    }

    // Handle Associates
    $pdo->prepare("DELETE FROM culprit_associates WHERE profile_id = ?")->execute([$profileId]);
    if (!empty($_POST['profileAssociates'])) {
        $lines = explode("\n", $_POST['profileAssociates']);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($name, $role) = explode(':', $line, 2);
                $stmt = $pdo->prepare("INSERT INTO culprit_associates (profile_id, name, role) VALUES (?, ?, ?)");
                $stmt->execute([$profileId, trim($name), trim($role)]);
            }
        }
    }
} else {
    // If profile unchecked, delete it? Or just ignore?
    // The UI logic implies if unchecked, it's not active.
    // We can delete it to be clean.
    $pdo->prepare("DELETE FROM culprit_profiles WHERE article_id = ?")->execute([$id]);
}

send_response(['success' => true, 'id' => $id]);
?>
