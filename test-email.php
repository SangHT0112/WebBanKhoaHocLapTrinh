<?php
/**
 * Test script to diagnose email sending issues
 * Run from terminal: php test-email.php
 * Or visit: http://localhost:3000/test-email.php (via browser)
 */

echo "<h1>Email & Checkout Diagnostic</h1>\n";
echo "<pre>\n";

// 1. Check PHPMailer installation
echo "=== Step 1: Check PHPMailer Installation ===\n";
if (file_exists('vendor/autoload.php')) {
    echo "✓ vendor/autoload.php exists\n";
    require_once 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✓ PHPMailer class found\n";
    } else {
        echo "✗ PHPMailer class NOT found (vendor may be corrupted)\n";
    }
} else {
    echo "✗ vendor/autoload.php NOT FOUND\n";
    echo "\nTo install PHPMailer, run:\n";
    echo "  composer require phpmailer/phpmailer\n\n";
    echo "If composer is not installed, download from: https://getcomposer.org\n";
    die;
}

// 2. Check database connection
echo "\n=== Step 2: Check Database Connection ===\n";
require_once 'db.php';
if ($conn) {
    echo "✓ Database connection OK\n";
    
    // Check if users table has email column
    $result = $conn->query("DESCRIBE users");
    $hasEmail = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'email') {
            $hasEmail = true;
            echo "✓ users.email column exists\n";
            break;
        }
    }
    if (!$hasEmail) {
        echo "✗ users.email column NOT FOUND - add it:\n";
        echo "  ALTER TABLE users ADD COLUMN email VARCHAR(255);\n";
    }
} else {
    echo "✗ Database connection failed\n";
    die;
}

// 3. Check file paths
echo "\n=== Step 3: Check File Paths ===\n";
$project_root = dirname(__DIR__);
$upload_courses = $project_root . "/uploads/courses";
$upload_temp = $project_root . "/uploads/temp";

echo "Project root: {$project_root}\n";
echo "Courses dir: {$upload_courses}\n";
if (is_dir($upload_courses)) {
    echo "✓ Courses directory exists\n";
    $courses = array_diff(scandir($upload_courses), ['.', '..']);
    foreach ($courses as $course_id) {
        $course_path = $upload_courses . "/{$course_id}/files";
        if (is_dir($course_path)) {
            $files = array_diff(scandir($course_path), ['.', '..']);
            echo "  ✓ Course {$course_id}: " . count($files) . " files\n";
        }
    }
} else {
    echo "✗ Courses directory does NOT exist\n";
}

if (!is_dir($upload_temp)) {
    if (@mkdir($upload_temp, 0777, true)) {
        echo "✓ Created temp directory: {$upload_temp}\n";
    } else {
        echo "✗ Cannot create temp directory\n";
    }
} else {
    echo "✓ Temp directory exists\n";
}

// 4. Test sample ZIP creation
echo "\n=== Step 4: Test ZIP Creation ===\n";
$test_zip = $upload_temp . "/test.zip";
$zip = new ZipArchive();
$result = $zip->open($test_zip, ZipArchive::CREATE);
if ($result === TRUE) {
    $zip->addFromString('test.txt', 'Hello from ZIP test');
    $zip->close();
    echo "✓ ZIP creation works - created: {$test_zip}\n";
    if (file_exists($test_zip)) {
        echo "✓ ZIP file exists: " . filesize($test_zip) . " bytes\n";
        unlink($test_zip);
        echo "✓ Test ZIP deleted\n";
    }
} else {
    echo "✗ ZIP creation failed with code: {$result}\n";
}

// 5. Check SMTP credentials (optional)
echo "\n=== Step 5: SMTP Configuration (in checkout.php) ===\n";
echo "Current SMTP settings:\n";
echo "  Host: smtp.gmail.com\n";
echo "  Port: 587\n";
echo "  Username: huynhtsang2004@gmail.com\n";
echo "  Password: xirlsuwpvwplymah (app-specific password for Gmail)\n";
echo "\nNOTE: If you changed Gmail password, update these values in checkout.php\n";
echo "Gmail App Password setup:\n";
echo "  1. Go to https://myaccount.google.com/apppasswords\n";
echo "  2. Generate new app password (use 'Mail' + 'Windows')\n";
echo "  3. Copy the 16-char password\n";
echo "  4. Update the Password field in checkout.php\n";

// 6. Check PHP extensions
echo "\n=== Step 6: PHP Extensions ===\n";
$required = ['curl', 'openssl'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ {$ext} extension loaded\n";
    } else {
        echo "✗ {$ext} extension NOT loaded - needed for SMTP\n";
    }
}

// 7. Summary
echo "\n=== Summary ===\n";
echo "If all checks pass, email sending should work after payment.\n";
echo "Check error_log or PHP logs if emails still fail:\n";
echo "  - Windows (XAMPP): C:\\xampp\\apache\\logs\\error.log\n";
echo "  - Linux: /var/log/apache2/error.log or tail -f /var/log/php-fpm.log\n";
echo "\n</pre>\n";
?>
