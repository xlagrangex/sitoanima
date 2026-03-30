<?php
/**
 * Router for PHP built-in server.
 * Handles WordPress pretty permalinks.
 */

$uri = urldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

// Serve static files directly
if ( $uri !== '/' && file_exists( __DIR__ . $uri ) ) {
    // Let PHP serve the file with correct MIME type
    $ext = pathinfo( $uri, PATHINFO_EXTENSION );
    $mime_types = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ico'  => 'image/x-icon',
    ];
    if ( isset( $mime_types[ $ext ] ) ) {
        header( 'Content-Type: ' . $mime_types[ $ext ] );
        readfile( __DIR__ . $uri );
        return true;
    }
    return false;
}

// Route everything else through WordPress
$_SERVER['PHP_SELF'] = '/index.php';
require __DIR__ . '/index.php';
