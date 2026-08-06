<?php
// Standalone endpoint: list all WordPress multisite URLs via direct URL access.

$wp_loaded = false;
$wp_load_candidates = array(
    __DIR__ . '/wp-load.php',
    dirname(__DIR__) . '/wp-load.php',
);

foreach ($wp_load_candidates as $wp_load_path) {
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
        $wp_loaded = function_exists('get_sites') && function_exists('home_url');
        break;
    }
}

$site_urls = array();

$is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$scheme = $is_https ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '/site-list.php';
$base_url = $scheme . '://' . $host . $path;
$csv_url = $base_url . '?download=excel';
$json_url = $base_url . '?json=1';

if ($wp_loaded && is_multisite()) {
    $sites = get_sites(
        array(
            'number' => 0,
            'deleted' => 0,
            'spam' => 0,
            'archived' => 0,
        )
    );

    if (!empty($sites)) {
        foreach ($sites as $site) {
            $site_urls[] = get_site_url($site->blog_id);
        }
    }
} elseif ($wp_loaded) {
    $site_urls[] = home_url('/');
}

if (isset($_GET['download']) && $_GET['download'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=network-site-urls-' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Site URL'));

    foreach ($site_urls as $site_url) {
        fputcsv($output, array($site_url));
    }

    fclose($output);
    exit;
}

if (isset($_GET['json']) && $_GET['json'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        array(
            'success' => $wp_loaded,
            'count' => count($site_urls),
            'urls' => array_values($site_urls),
            'message' => $wp_loaded ? '' : 'WordPress could not be loaded.',
        )
    );
    exit;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Network Site URLs</title>
    <style>
        :root {
            --bg-1: #f6f8fb;
            --bg-2: #e9eef7;
            --panel: #ffffff;
            --panel-border: #d7dfea;
            --text-main: #1d2a3a;
            --text-muted: #58677a;
            --accent: #0f6cbd;
            --accent-hover: #005da6;
            --success: #1f7a37;
            --shadow: 0 20px 45px rgba(16, 36, 64, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande", sans-serif;
            color: var(--text-main);
            background:
                radial-gradient(1200px 500px at 85% -10%, #d6e9ff 0%, transparent 55%),
                radial-gradient(900px 500px at -10% 110%, #e8f5e9 0%, transparent 55%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2));
            padding: 32px 20px;
        }

        .wrap {
            max-width: 1020px;
            margin: 0 auto;
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            padding: 26px 26px 20px;
            background: linear-gradient(100deg, #0f6cbd 0%, #1a7f52 100%);
            color: #ffffff;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.4rem, 1.8vw + 1rem, 2rem);
            letter-spacing: 0.2px;
        }

        .subtitle {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.96rem;
        }

        .content {
            padding: 22px 24px 26px;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border: 1px solid #9db1c8;
            border-radius: 10px;
            background: #f9fbfe;
            color: #113253;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .btn:hover {
            background: #edf4fc;
            border-color: #7f99b8;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
        }

        .status {
            min-height: 20px;
            font-size: 14px;
            color: var(--success);
            margin-bottom: 10px;
        }

        .count {
            margin: 0 0 14px;
            font-size: 0.92rem;
            color: var(--text-muted);
        }

        .usage-panel {
            margin: 0 0 18px;
            padding: 14px;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            background: #f8fbff;
        }

        .usage-title {
            margin: 0 0 10px;
            font-size: 0.98rem;
            font-weight: 700;
            color: #113253;
        }

        .usage-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px;
            align-items: start;
            padding: 8px 0;
            border-top: 1px solid #e4ebf3;
        }

        .usage-row:first-of-type {
            border-top: 0;
            padding-top: 0;
        }

        .usage-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .usage-link {
            color: #12426c;
            text-decoration: none;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .usage-link:hover {
            text-decoration: underline;
        }

        .usage-note {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .site-list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .site-item {
            border: 1px solid #dbe3ee;
            background: #ffffff;
            border-radius: 12px;
            padding: 11px 14px;
            line-height: 1.5;
        }

        .site-item a {
            color: #12426c;
            text-decoration: none;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .site-item a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            body { padding: 16px 12px; }
            .header { padding: 20px 16px 16px; }
            .content { padding: 14px 12px 16px; }
            .toolbar { gap: 8px; }
            .btn { width: 100%; }
            .usage-row { grid-template-columns: 1fr; gap: 4px; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Network Site URLs</h1>
        <p class="subtitle">Quickly browse, copy, or export every site URL in this WordPress network.</p>
    </div>
    <div class="content">
    <div class="toolbar">
        <a class="btn" href="?download=excel">Download for Excel (CSV)</a>
        <a class="btn" href="/shortcoder-report.php">Shortcode List</a>
        <button id="copyAllBtn" class="btn btn-primary" type="button">Copy All URLs</button>
    </div>
    <p class="count">Total URLs: <?php echo count($site_urls); ?></p>
    <div class="usage-panel">
        <p class="usage-title">Access Modes</p>
        <div class="usage-row">
            <div class="usage-label">HTML</div>
            <div>
                <a class="usage-link" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?></a>
                <p class="usage-note">Open this page in the browser to view the styled list.</p>
            </div>
        </div>
        <div class="usage-row">
            <div class="usage-label">Excel</div>
            <div>
                <a class="usage-link" href="<?php echo htmlspecialchars($csv_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($csv_url, ENT_QUOTES, 'UTF-8'); ?></a>
                <p class="usage-note">Downloads a CSV file that opens in Excel.</p>
            </div>
        </div>
        <div class="usage-row">
            <div class="usage-label">JSON</div>
            <div>
                <a class="usage-link" href="<?php echo htmlspecialchars($json_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($json_url, ENT_QUOTES, 'UTF-8'); ?></a>
                <p class="usage-note">Returns the network site URLs as JSON.</p>
            </div>
        </div>
    </div>
    <div id="copyStatus" class="status" aria-live="polite"></div>
    <ul class="site-list">
        <?php
        if (!$wp_loaded) {
            echo '<li class="site-item">WordPress could not be loaded. Keep this file in WordPress root (same folder as wp-config.php).</li>';
        } elseif (!empty($site_urls)) {
            foreach ($site_urls as $site_url) {
                echo '<li class="site-item"><a href="' . esc_url($site_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($site_url) . '</a></li>';
            }
        } else {
            echo '<li class="site-item">No sites found in this network.</li>';
        }
        ?>
    </ul>
    </div>
</div>
<script>
    (function () {
        var copyButton = document.getElementById('copyAllBtn');
        var status = document.getElementById('copyStatus');
        var urls = <?php echo wp_json_encode(array_values($site_urls)); ?>;

        if (!copyButton) {
            return;
        }

        copyButton.addEventListener('click', function () {
            if (!urls.length) {
                status.textContent = 'No URLs available to copy.';
                return;
            }

            var text = urls.join('\n');

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    status.textContent = 'Copied ' + urls.length + ' URL(s) to clipboard.';
                }).catch(function () {
                    status.textContent = 'Copy failed. Please copy manually from the list.';
                });
                return;
            }

            var textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();

            try {
                var copied = document.execCommand('copy');
                status.textContent = copied ? 'Copied ' + urls.length + ' URL(s) to clipboard.' : 'Copy failed. Please copy manually from the list.';
            } catch (error) {
                status.textContent = 'Copy failed. Please copy manually from the list.';
            }

            document.body.removeChild(textarea);
        });
    })();
</script>
</body>
</html>
