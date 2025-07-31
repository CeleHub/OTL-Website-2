<?php
// SQLite Database setup script for OTL Website Image Upload System - DEBUG VERSION

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SQLite Setup Debug ===\n\n";

try {
    echo "Step 1: Creating database directory...\n";
    // Create database directory if it doesn't exist
    $dbDir = '../database/';
    if (!file_exists($dbDir)) {
        if (mkdir($dbDir, 0755, true)) {
            echo "✅ Database directory created: $dbDir\n";
        } else {
            throw new Exception("Failed to create database directory: $dbDir");
        }
    } else {
        echo "✅ Database directory already exists: $dbDir\n";
    }
    
    echo "\nStep 2: Creating SQLite database...\n";
    // Create SQLite database
    $dbPath = $dbDir . 'otl_website.db';
    echo "Database path: $dbPath\n";
    
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ SQLite database connection successful\n";
    
    echo "\nStep 3: Creating users table...\n";
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Users table created successfully\n";
    
    echo "\nStep 4: Creating images table...\n";
    // Create images table
    $sql = "CREATE TABLE IF NOT EXISTS images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT NOT NULL,
        original_name TEXT NOT NULL,
        description TEXT,
        category TEXT,
        uploaded_by INTEGER,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "✅ Images table created successfully\n";
    
    echo "\nStep 5: Creating default admin user...\n";
    // Create default admin user
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkUser->execute(['admin']);
    if (!$checkUser->fetch()) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertUser = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $insertUser->execute(['admin', $hashedPassword]);
        echo "✅ Default admin user created\n";
        echo "   Username: admin\n";
        echo "   Password: admin123\n";
    } else {
        echo "✅ Admin user already exists\n";
    }
    
    echo "\nStep 6: Creating uploads directory...\n";
    // Create uploads directory
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo "✅ Uploads directory created: $uploadDir\n";
        } else {
            throw new Exception("Failed to create uploads directory: $uploadDir");
        }
    } else {
        echo "✅ Uploads directory already exists: $uploadDir\n";
    }
    
    echo "\nStep 7: Testing database functionality...\n";
    // Test database functionality
    $testUser = $pdo->prepare("SELECT username FROM users WHERE username = ?");
    $testUser->execute(['admin']);
    $user = $testUser->fetch();
    if ($user) {
        echo "✅ Database read test successful\n";
    } else {
        throw new Exception("Database read test failed");
    }
    
    echo "\n🎉 Setup completed successfully!\n";
    echo "Database file: $dbPath\n";
    echo "Uploads directory: $uploadDir\n";
    echo "You can now access the admin panel at: admin/login-sqlite.php\n";
    echo "Default credentials: admin / admin123\n";
    
} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} catch(Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
}
?> 