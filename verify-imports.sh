#!/bin/bash

# Import Paths Verification Script
# This script checks that all necessary import paths are present in PHP files

echo "==================================================="
echo "Import Paths & Lucide Icons Verification"
echo "==================================================="
echo ""

ERRORS=0
WARNINGS=0

# Function to check if a file contains a pattern
check_pattern() {
    local file=$1
    local pattern=$2
    local description=$3
    
    if grep -q "$pattern" "$file" 2>/dev/null; then
        echo "✓ $file - $description"
        return 0
    else
        echo "✗ $file - MISSING: $description"
        ERRORS=$((ERRORS+1))
        return 1
    fi
}

echo "Checking Admin Pages..."
echo "------------------------"
check_pattern "public/admin/includes/header.php" "lucide.js" "Lucide JS import"
check_pattern "public/admin/includes/header.php" "styles.css" "Styles CSS import"
check_pattern "public/admin/includes/header.php" "custom.css" "Custom CSS import"
check_pattern "public/admin/includes/footer.php" "lucide.createIcons" "Lucide icon initialization"

check_pattern "public/admin/documents.php" "includes/footer.php" "Footer include in documents"
check_pattern "public/admin/documents.php" "lucide.createIcons" "Lucide creation in documents"

echo ""
echo "Checking Public Pages..."
echo "------------------------"
check_pattern "public/index.php" "styles.css" "Index - Styles CSS import"
check_pattern "public/index.php" "custom.css" "Index - Custom CSS import"
check_pattern "public/index.php" "lucide.js" "Index - Lucide JS import"
check_pattern "public/index.php" "lucide.createIcons" "Index - Lucide initialization"

check_pattern "public/register.php" "styles.css" "Register - Styles CSS import"
check_pattern "public/register.php" "custom.css" "Register - Custom CSS import"
check_pattern "public/register.php" "lucide.js" "Register - Lucide JS import"

check_pattern "public/login/index.php" "styles.css" "Login - Styles CSS import"
check_pattern "public/login/index.php" "custom.css" "Login - Custom CSS import"
check_pattern "public/login/index.php" "lucide.js" "Login - Lucide JS import"

check_pattern "public/read/index.php" "styles.css" "Read - Styles CSS import"
check_pattern "public/read/index.php" "custom.css" "Read - Custom CSS import"
check_pattern "public/read/index.php" "lucide.js" "Read - Lucide JS import"
check_pattern "public/read/index.php" "lucide.createIcons" "Read - Lucide initialization"

echo ""
echo "Checking Dashboard Pages..."
echo "----------------------------"
check_pattern "public/dashboard/includes/header.php" "styles.css" "Dashboard - Styles CSS import"
check_pattern "public/dashboard/includes/header.php" "custom.css" "Dashboard - Custom CSS import"
check_pattern "public/dashboard/includes/header.php" "lucide.js" "Dashboard - Lucide JS import"

echo ""
echo "Checking CSS Files Exist..."
echo "----------------------------"
if [ -f "public/assets/css/styles.css" ]; then
    LINES=$(wc -l < public/assets/css/styles.css)
    echo "✓ public/assets/css/styles.css ($LINES lines)"
else
    echo "✗ public/assets/css/styles.css NOT FOUND"
    ERRORS=$((ERRORS+1))
fi

if [ -f "public/assets/css/custom.css" ]; then
    LINES=$(wc -l < public/assets/css/custom.css)
    echo "✓ public/assets/css/custom.css ($LINES lines)"
else
    echo "✗ public/assets/css/custom.css NOT FOUND"
    ERRORS=$((ERRORS+1))
fi

if [ -f "public/assets/js/lucide.js" ]; then
    LINES=$(wc -l < public/assets/js/lucide.js)
    echo "✓ public/assets/js/lucide.js ($LINES lines)"
else
    echo "✗ public/assets/js/lucide.js NOT FOUND"
    ERRORS=$((ERRORS+1))
fi

echo ""
echo "==================================================="
if [ $ERRORS -eq 0 ]; then
    echo "✅ All import paths verified successfully!"
else
    echo "❌ Found $ERRORS issues to fix"
fi
echo "==================================================="

exit $ERRORS
