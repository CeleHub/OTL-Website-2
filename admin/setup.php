<?php
// Database setup script for OTL Website Image Upload System

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Create database if it doesn't exist
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS otl_website");
    echo "✅ Database 'otl_website' created successfully\n";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=otl_website", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Users table created successfully\n";
    
    // Create images table
    $sql = "CREATE TABLE IF NOT EXISTS images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100),
        uploaded_by INT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    
    echo "\n🎉 Setup completed successfully!\n";
    echo "You can now access the admin panel at: admin/login.php\n";
    echo "Default credentials: admin / admin123\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 