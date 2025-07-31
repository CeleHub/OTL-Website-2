<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
    die("Connection failed: " . $e->getMessage());
}

// Create images table if it doesn't exist
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

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle image deletion
if (isset($_POST['delete_image'])) {
    $image_id = $_POST['image_id'];
    $stmt = $pdo->prepare("SELECT filename FROM images WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch();
    
    if ($image) {
        $filepath = "../uploads/" . $image['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        $deleteStmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
        $deleteStmt->execute([$image_id]);
        $success = "Image deleted successfully!";
    }
}

// Get all uploaded images
$stmt = $pdo->prepare("SELECT i.*, u.username FROM images i LEFT JOIN users u ON i.uploaded_by = u.id ORDER BY i.uploaded_at DESC");
$stmt->execute();
$images = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - OTL Website</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <style>
        .admin-header {
            background: #1e3a8a;
            color: white;
            padding: 1rem 0;
        }
        .admin-sidebar {
            background: #f8f9fa;
            min-height: calc(100vh - 80px);
            padding: 2rem;
        }
        .admin-content {
            padding: 2rem;
        }
        .upload-area {
            border: 2px dashed #1e3a8a;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: #3b82f6;
            background: #e3f2fd;
        }
        .upload-area.dragover {
            border-color: #3b82f6;
            background: #e3f2fd;
        }
        .image-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .image-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .image-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .btn-primary {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .nav-link {
            color: #1e3a8a;
        }
        .nav-link.active {
            background-color: #1e3a8a !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">OTL Admin Dashboard</h1>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="?logout=1" class="btn btn-outline-light btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="admin-sidebar">
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#upload" data-bs-toggle="tab">
                            <i class="fa fa-upload"></i> Upload Images
                        </a>
                        <a class="nav-link" href="#manage" data-bs-toggle="tab">
                            <i class="fa fa-images"></i> Manage Images
                        </a>
                        <a class="nav-link" href="#settings" data-bs-toggle="tab">
                            <i class="fa fa-cog"></i> Settings
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="admin-content">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="tab-content">
                        <!-- Upload Tab -->
                        <div class="tab-pane fade show active" id="upload">
                            <h2 class="mb-4">Upload New Images</h2>
                            
                            <div class="upload-area" id="uploadArea">
                                <i class="fa fa-cloud-upload fa-3x text-muted mb-3"></i>
                                <h4>Drag & Drop Images Here</h4>
                                <p class="text-muted">or click to select files</p>
                                <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
                                <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                    Choose Files
                                </button>
                            </div>

                            <div id="uploadProgress" class="mt-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Uploading...</small>
                            </div>

                            <div id="uploadResults" class="mt-3"></div>
                        </div>

                        <!-- Manage Tab -->
                        <div class="tab-pane fade" id="manage">
                            <h2 class="mb-4">Manage Images</h2>
                            
                            <div class="row">
                                <?php foreach ($images as $image): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="image-card">
                                            <img src="../uploads/<?php echo htmlspecialchars($image['filename']); ?>" 
                                                 class="image-preview" 
                                                 alt="<?php echo htmlspecialchars($image['original_name']); ?>">
                                            <div class="p-3">
                                                <h6><?php echo htmlspecialchars($image['original_name']); ?></h6>
                                                <p class="text-muted small">
                                                    <?php echo htmlspecialchars($image['description'] ?? 'No description'); ?>
                                                </p>
                                                <p class="text-muted small">
                                                    Category: <?php echo htmlspecialchars($image['category'] ?? 'Uncategorized'); ?>
                                                </p>
                                                <p class="text-muted small">
                                                    Uploaded: <?php echo date('M j, Y', strtotime($image['uploaded_at'])); ?>
                                                </p>
                                                <div class="d-flex justify-content-between">
                                                    <a href="../uploads/<?php echo htmlspecialchars($image['filename']); ?>" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                        <button type="submit" name="delete_image" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this image?')">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Settings Tab -->
                        <div class="tab-pane fade" id="settings">
                            <h2 class="mb-4">Settings</h2>
                            <div class="card">
                                <div class="card-body">
                                    <h5>System Information</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Total Images:</strong> <?php echo count($images); ?></li>
                                        <li><strong>Upload Directory:</strong> ../uploads/</li>
                                        <li><strong>Max File Size:</strong> 5MB</li>
                                        <li><strong>Allowed Formats:</strong> JPG, PNG, GIF</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery-1.11.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadResults = document.getElementById('uploadResults');

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            uploadProgress.style.display = 'block';
            uploadResults.innerHTML = '';

            Array.from(files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    uploadFile(file, index);
                }
            });
        }

        function uploadFile(file, index) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('description', prompt('Enter description for ' + file.name) || '');
            formData.append('category', prompt('Enter category for ' + file.name) || 'Uncategorized');

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    uploadResults.innerHTML += `
                        <div class="alert alert-success">
                            <i class="fa fa-check"></i> ${file.name} uploaded successfully!
                        </div>
                    `;
                } else {
                    uploadResults.innerHTML += `
                        <div class="alert alert-danger">
                            <i class="fa fa-times"></i> Error uploading ${file.name}: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                uploadResults.innerHTML += `
                    <div class="alert alert-danger">
                        <i class="fa fa-times"></i> Error uploading ${file.name}: ${error.message}
                    </div>
                `;
            })
            .finally(() => {
                if (index === 0) {
                    setTimeout(() => {
                        uploadProgress.style.display = 'none';
                        location.reload();
                    }, 2000);
                }
            });
        }
    </script>
</body>
</html> 