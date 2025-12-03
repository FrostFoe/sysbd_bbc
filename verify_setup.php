<?php
/**
 * BreachTimes Interconnected System - Setup Verification
 * Run this in browser: /php-clone/verify_setup.php
 */

require_once __DIR__ . "/includes/db.php";

$checks = [
    "success" => [],
    "warnings" => [],
    "errors" => [],
];

// Check 1: Database tables exist
try {
    $stmt = $pdo->query("DESC articles");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (in_array("lang", $cols)) {
        $checks["success"][] = "✅ articles table has 'lang' column (unified)";
    } else {
        $checks["errors"][] = "❌ articles table missing 'lang' column - need to run migration";
    }
} catch (Exception $e) {
    $checks["errors"][] = "❌ articles table doesn't exist: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("DESC sections");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (in_array("lang", $cols)) {
        $checks["success"][] = "✅ sections table has 'lang' column (unified)";
    } else {
        $checks["errors"][] = "❌ sections table missing 'lang' column - need to run migration";
    }
} catch (Exception $e) {
    $checks["errors"][] = "❌ sections table doesn't exist: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("DESC comments");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (in_array("lang", $cols)) {
        $checks["success"][] = "✅ comments table has 'lang' column (unified)";
    } else {
        $checks["errors"][] = "❌ comments table missing 'lang' column - need to run migration";
    }
} catch (Exception $e) {
    $checks["errors"][] = "❌ comments table doesn't exist: " . $e->getMessage();
}

// Check 2: Old tables don't exist (shouldn't have duplicates)
foreach (["articles_en", "sections_en", "comments_en"] as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $checks["warnings"][] = "⚠️ Old table '$table' still exists - consider dropping after migration";
    } catch (Exception $e) {
        // Good - old table doesn't exist
    }
}

// Check 3: Sample data check
try {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM articles WHERE lang='bn'");
    $row = $stmt->fetch();
    $bn_count = $row["cnt"] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM articles WHERE lang='en'");
    $row = $stmt->fetch();
    $en_count = $row["cnt"] ?? 0;
    
    if ($bn_count > 0 && $en_count > 0) {
        $checks["success"][] = "✅ Sample data exists: $bn_count Bengali articles, $en_count English articles";
    } elseif ($bn_count === 0 && $en_count === 0) {
        $checks["warnings"][] = "ℹ️ No articles yet (empty database) - this is fine for fresh start";
    } else {
        $checks["warnings"][] = "⚠️ Imbalanced data: $bn_count Bengali, $en_count English - consider populating both";
    }
} catch (Exception $e) {
    $checks["warnings"][] = "ℹ️ Could not check article counts";
}

// Check 4: API files exist
$api_files = [
    "get_categories.php",
    "save_article.php",
    "delete_article.php",
    "post_comment.php",
    "get_sections.php",
    "save_section.php",
    "delete_section.php",
];

foreach ($api_files as $file) {
    if (file_exists(__DIR__ . "/api/$file")) {
        $checks["success"][] = "✅ API file exists: /api/$file";
    } else {
        $checks["errors"][] = "❌ Missing API file: /api/$file";
    }
}

// Check 5: Core files updated
$core_files = [
    "get_data.php",
];

foreach ($core_files as $file) {
    if (file_exists(__DIR__ . "/$file")) {
        $content = file_get_contents(__DIR__ . "/$file");
        if (strpos($content, "WHERE lang") !== false) {
            $checks["success"][] = "✅ Core file updated: /$file (uses 'WHERE lang')";
        } else {
            $checks["errors"][] = "❌ Core file not updated: /$file (missing 'WHERE lang' clause)";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BreachTimes - Interconnected System Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .check-section {
            margin-bottom: 30px;
        }
        .check-section h2 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 12px 16px;
            margin: 8px 0;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
        }
        .success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }
        .warning {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            color: #e65100;
        }
        .error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #c62828;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .summary-box {
            padding: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .summary-box h3 {
            margin: 0;
            font-size: 24px;
        }
        .summary-box p {
            margin: 5px 0 0 0;
            opacity: 0.7;
        }
        .summary-success { background: #e8f5e9; color: #2e7d32; }
        .summary-warning { background: #fff3e0; color: #e65100; }
        .summary-error { background: #ffebee; color: #c62828; }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 10px;
        }
        .badge-ok { background: #4caf50; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .badge-error { background: #f44336; color: white; }
        .footer {
            padding: 20px 30px;
            background: #f5f5f5;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BreachTimes Interconnected System</h1>
            <p>Setup Verification Report</p>
        </div>
        
        <div class="content">
            <div class="summary">
                <div class="summary-box summary-success">
                    <h3><?php echo count($checks["success"]); ?></h3>
                    <p>✅ Passed</p>
                </div>
                <div class="summary-box summary-warning">
                    <h3><?php echo count($checks["warnings"]); ?></h3>
                    <p>⚠️ Warnings</p>
                </div>
                <div class="summary-box summary-error">
                    <h3><?php echo count($checks["errors"]); ?></h3>
                    <p>❌ Errors</p>
                </div>
            </div>

            <?php if (!empty($checks["success"])): ?>
            <div class="check-section">
                <h2>✅ Passed Checks</h2>
                <?php foreach ($checks["success"] as $item): ?>
                    <div class="check-item success"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($checks["warnings"])): ?>
            <div class="check-section">
                <h2>⚠️ Warnings</h2>
                <?php foreach ($checks["warnings"] as $item): ?>
                    <div class="check-item warning"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($checks["errors"])): ?>
            <div class="check-section">
                <h2>❌ Errors</h2>
                <?php foreach ($checks["errors"] as $item): ?>
                    <div class="check-item error"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="check-section">
                <h2>📋 Overall Status</h2>
                <?php 
                    if (count($checks["errors"]) === 0 && count($checks["warnings"]) === 0) {
                        $status = "✅ System Ready!";
                        $badge = "badge-ok";
                    } elseif (count($checks["errors"]) === 0) {
                        $status = "⚠️ System Operational (with warnings)";
                        $badge = "badge-warning";
                    } else {
                        $status = "❌ System Needs Attention";
                        $badge = "badge-error";
                    }
                ?>
                <p><?php echo $status; ?></p>
                <span class="status-badge <?php echo $badge; ?>"><?php echo $status; ?></span>
            </div>
        </div>

        <div class="footer">
            <p>
                For detailed setup instructions, see: <code>IMPLEMENTATION_COMPLETE.md</code><br>
                For migration help, see: <code>MIGRATION_SCRIPT.sql</code> and <code>INTERCONNECTED_MIGRATION.md</code>
            </p>
        </div>
    </div>
</body>
</html>
