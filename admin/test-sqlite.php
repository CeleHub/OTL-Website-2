<?php
echo "=== PHP SQLite Test ===\n\n";

// Check PHP version
echo "PHP Version: " . phpversion() . "\n";

// Check if SQLite extension is available
if (extension_loaded('pdo_sqlite')) {
    echo "✅ PDO SQLite extension is available\n";
} else {
    echo "❌ PDO SQLite extension is NOT available\n";
}

// Check if PDO is available
if (extension_loaded('pdo')) {
    echo "✅ PDO extension is available\n";
} else {
    echo "❌ PDO extension is NOT available\n";
}

// Check if we can create a test database
try {
    $testDb = 'test_sqlite.db';
    $pdo = new PDO("sqlite:$testDb");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test creating a table
    $pdo->exec("CREATE TABLE IF NOT EXISTS test (id INTEGER PRIMARY KEY, name TEXT)");
    echo "✅ SQLite database creation works\n";
    
    // Test inserting data
    $stmt = $pdo->prepare("INSERT INTO test (name) VALUES (?)");
    $stmt->execute(['test_value']);
    echo "✅ SQLite data insertion works\n";
    
    // Test reading data
    $stmt = $pdo->prepare("SELECT * FROM test WHERE name = ?");
    $stmt->execute(['test_value']);
    $result = $stmt->fetch();
    if ($result) {
        echo "✅ SQLite data reading works\n";
    }
    
    // Clean up test database
    unlink($testDb);
    echo "✅ Test completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ SQLite test failed: " . $e->getMessage() . "\n";
}

// Check directory permissions
$testDir = '../database/';
if (!file_exists($testDir)) {
    if (mkdir($testDir, 0755, true)) {
        echo "✅ Database directory created successfully\n";
    } else {
        echo "❌ Failed to create database directory\n";
    }
} else {
    if (is_writable($testDir)) {
        echo "✅ Database directory is writable\n";
    } else {
        echo "❌ Database directory is NOT writable\n";
    }
}

echo "\n=== Test Complete ===\n";
?> 