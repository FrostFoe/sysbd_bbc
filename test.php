<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM articles");
    $result = $stmt->fetch();
    echo "Database connected. Articles count: " . $result['count'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>