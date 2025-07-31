<?php
// SQLite Database setup script for OTL Website Image Upload System

try {
    // Create database directory if it doesn't exist
    $dbDir = '../database/';
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    // Create SQLite database
    $dbPath = $dbDir . 'otl_website.db';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ SQLite database created successfully\n";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Users table created successfully\n";
    
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
    
    // Create uploads directory
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✅ Uploads directory created\n";
    } else {
        echo "✅ Uploads directory already exists\n";
    }
    
    echo "\n🎉 SQLite setup completed successfully!\n";
    echo "You can now access the admin panel at: admin/login-sqlite.php\n";
    echo "Default credentials: admin / admin123\n";
    echo "Database file: $dbPath\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 