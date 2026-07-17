<?php
/**
 * Router script for PHP Built-in Development Server (php -S)
 * Simulates Apache .htaccess Rewrite Rules
 * 
 * Usage: php -S localhost:8000 router.php
 */

$req_uri = $_SERVER['REQUEST_URI'];
$req_path = parse_url($req_uri, PHP_URL_PATH);

// 1. Dynamic Sitemap Routing
if ($req_path === '/sitemap.xml') {
    include __DIR__ . '/sitemap.php';
    return true;
}

// 2. Dynamic Blog Article Clean URL mappings
// Must be checked BEFORE we check for physical directories
// Matches /blog/some-slug/ but NOT /blog/ alone or /blog/index.php
if (preg_match('#^/blog/([a-zA-Z0-9][a-zA-Z0-9\-]+)/?$#', $req_path, $matches)) {
    $_GET['slug'] = $matches[1];
    include __DIR__ . '/blog/article.php';
    return true;
}

// 3. Dynamic Location Detail mappings
if (preg_match('#^/lokasi-urban-office/([a-zA-Z0-9][a-zA-Z0-9\-]+)/?$#', $req_path, $matches)) {
    $_GET['slug'] = $matches[1];
    include __DIR__ . '/lokasi-urban-office/detail.php';
    return true;
}

// 3.5 Dynamic Virtual Office Branch Landing Page mappings
// /virtual-office-surabaya/ itself is a real folder (MERR branch) and is excluded here
// so it keeps being served by the physical-directory check further below.
if (preg_match('#^/virtual-office-([a-zA-Z0-9][a-zA-Z0-9\-]+)/?$#', $req_path, $matches) && $matches[1] !== 'surabaya') {
    $_GET['branch'] = $matches[1];
    include __DIR__ . '/virtual-office-surabaya/index.php';
    return true;
}

// 4. Category Clean URL mapping
if (preg_match('#^/category/([a-zA-Z0-9][a-zA-Z0-9\-]+)/?$#', $req_path, $matches)) {
    $_GET['slug'] = $matches[1];
    include __DIR__ . '/blog/category.php';
    return true;
}

// 5. Tag Clean URL mapping
if (preg_match('#^/tag/([a-zA-Z0-9][a-zA-Z0-9\-]+)/?$#', $req_path, $matches)) {
    $_GET['slug'] = $matches[1];
    include __DIR__ . '/blog/tag.php';
    return true;
}

// -------------------------------------------------------------------
// Serve physical files (CSS, JS, images, PHP pages) using built-in server
// -------------------------------------------------------------------

// Decode URL-encoded characters (e.g. %20 for spaces in filenames)
$decoded_path = urldecode($req_path);
$file = __DIR__ . $decoded_path;

// If it's an actual file (e.g. .css, .js, .png, .php), serve it
if (is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    // For PHP files, include them
    if ($ext === 'php') {
        include $file;
        return true;
    }
    
    // For known static assets, set proper Content-Type and serve
    $mime_types = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'xml'  => 'application/xml',
        'txt'  => 'text/plain',
        'pdf'  => 'application/pdf',
    ];
    
    if (isset($mime_types[$ext])) {
        header('Content-Type: ' . $mime_types[$ext]);
        readfile($file);
        return true;
    }
    
    // For other file types, let the built-in server handle
    return false;
}

// If it's a directory, check for index.php inside it
$dir = rtrim($file, '/\\');
if (is_dir($dir)) {
    if (is_file($dir . '/index.php')) {
        include $dir . '/index.php';
        return true;
    }
    if (is_file($dir . '/index.html')) {
        return false;
    }
}

// Fallback: 404
header("HTTP/1.1 404 Not Found");
echo "<h1>404 Not Found</h1><p>The requested URL was not found on this server.</p>";
return true;
