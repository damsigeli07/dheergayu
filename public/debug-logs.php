<?php
// public/debug-logs.php
// IMPORTANT: Remove this file from production!

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Logs</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
        }
        .log-section {
            background: #252526;
            border: 1px solid #3e3e42;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .log-title {
            color: #ce9178;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .log-content {
            background: #1e1e1e;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 12px;
            line-height: 1.4;
            max-height: 400px;
            overflow-y: auto;
        }
        .error {
            color: #f48771;
        }
        .success {
            color: #4ec9b0;
        }
        button {
            background: #0e639c;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            margin: 5px 5px 5px 0;
            font-family: monospace;
        }
        button:hover {
            background: #1177bb;
        }
        .button-group {
            margin-bottom: 20px;
        }
        .info {
            background: #264f78;
            border-left: 4px solid #007acc;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Logs Viewer</h1>
        
        <div class="info">
            <strong>Note:</strong> This file should be deleted before going to production!
        </div>

        <div class="button-group">
            <button onclick="location.reload()">🔄 Refresh</button>
            <button onclick="clearAllLogs()">🗑️ Clear All Logs</button>
            <button onclick="downloadLogs()">⬇️ Download Logs</button>
        </div>

        <?php
        $logsDir = __DIR__ . '/../logs';

        // Check if logs directory exists
        if (!is_dir($logsDir)) {
            echo '<div class="log-section"><div class="error">❌ Logs directory not found at: ' . $logsDir . '</div></div>';
            echo '<div class="info">Please create the directory: <code>mkdir -p ' . $logsDir . '</code></div>';
            exit;
        }

        // Get all log files
        $logFiles = glob($logsDir . '/*.log');

        if (empty($logFiles)) {
            echo '<div class="log-section"><div class="error">⚠️ No log files found yet. Try booking a consultation to generate logs.</div></div>';
        } else {
            // Sort by modification time, newest first
            usort($logFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            foreach ($logFiles as $file) {
                $filename = basename($file);
                $content = file_get_contents($file);
                $size = filesize($file);
                $modified = date('Y-m-d H:i:s', filemtime($file));

                echo '<div class="log-section">';
                echo '<div class="log-title">📄 ' . htmlspecialchars($filename) . ' (' . round($size / 1024, 2) . ' KB) - Modified: ' . $modified . '</div>';
                
                if (empty($content)) {
                    echo '<div class="log-content"><span class="error">Empty log file</span></div>';
                } else {
                    // Highlight errors in red
                    $content = htmlspecialchars($content);
                    $content = preg_replace(
                        '/ERROR:|Exception:|Warning:|Fatal:/',
                        '<span class="error">$0</span>',
                        $content
                    );
                    $content = preg_replace(
                        '/success|Successfully|created|booked|Completed/i',
                        '<span class="success">$0</span>',
                        $content
                    );
                    
                    echo '<div class="log-content">' . $content . '</div>';
                }
                
                echo '</div>';
            }
        }
        ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #3e3e42; color: #888;">
            <p><strong>⚠️ Security Warning:</strong> This debug page exposes logs. Delete <code>debug-logs.php</code> before deploying to production!</p>
            <p><strong>Log Location:</strong> <code><?php echo $logsDir; ?></code></p>
        </div>
    </div>

    <script>
        function clearAllLogs() {
            if (confirm('Are you sure? This will delete all log files!')) {
                fetch('<?php echo $_SERVER["PHP_SELF"]; ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=clear'
                }).then(() => location.reload());
            }
        }

        function downloadLogs() {
            alert('Download functionality - copy logs manually or use FTP');
        }
    </script>

    <?php
    // Handle clearing logs
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'clear') {
        $files = glob($logsDir . '/*.log');
        foreach ($files as $file) {
            unlink($file);
        }
        echo '<script>alert("Logs cleared!");</script>';
    }
    ?>
</body>
</html>