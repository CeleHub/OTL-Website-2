<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'otl_website';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Security settings
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit();
}

$file = $_FILES['image'];
$description = $_POST['description'] ?? '';
$category = $_POST['category'] ?? 'Uncategorized';

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
        UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];
    
    $message = $errorMessages[$file['error']] ?? 'Unknown upload error';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check file size
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB']);
    exit();
}

// Check file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
    exit();
}

// Check file extension
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file extension']);
    exit();
}

// Generate unique filename
$originalName = $file['name'];
$filename = uniqid() . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit();
}

// Verify the uploaded file is actually an image
$imageInfo = getimagesize($filepath);
if (!$imageInfo) {
    unlink($filepath); // Delete the file
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Uploaded file is not a valid image']);
    exit();
}

// Save to database
try {
    $stmt = $pdo->prepare("INSERT INTO images (filename, original_name, description, category, uploaded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$filename, $originalName, $description, $category, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Image uploaded successfully',
        'filename' => $filename,
        'original_name' => $originalName
    ]);
} catch (PDOException $e) {
    unlink($filepath); // Delete the file if database insert fails
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save image information to database']);
    exit();
}
?> 