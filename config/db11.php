<?php
/**
 * Database connection.
 * Update these four values to match your hosting/MySQL account.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'u863796476_raisbookhouse');
define('DB_USER', 'u863796476_raisbookhouse');
define('DB_PASS', 'Coding1234+');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please check config/db.php — ' . $e->getMessage());
}

/**
 * Load site-wide settings (name, WhatsApp numbers, contact info, logo).
 * Returns an associative array so it can be used anywhere with $settings['field'].
 */
function get_settings($pdo) {
    static $settings = null;
    if ($settings === null) {
        $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
        $settings = $stmt->fetch();
        if (!$settings) {
            $settings = [
                'site_name' => 'Rais Doultana Book House',
                'tagline' => 'Wholesale Dealers, Importers & Exporters of Books',
                'logo' => null,
                'whatsapp_1' => '923002873276',
                'whatsapp_2' => '923019469494',
                'phone' => '042-37229494',
                'email' => 'doultanaraees@gmail.com',
                'address_main' => 'Shop No. 3, Khalid Market, 40 Urdu Bazar, Lahore',
                'address_branch' => 'Branch: Khanewal Doultana Plaza',
                'boards_publishers' => 'Punjab Textbook Board,Federal Textbook Board,Oxford University Press,Cambridge University Press,National Book Foundation,Unique Notes,Topics Academy',
            ];
        }
    }
    return $settings;
}

/** Helper: turn "Some Category Name" into "some-category-name" */
function make_slug($text) {
    $text = trim($text);
    $text = preg_replace('/[^A-Za-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return strtolower($text) ?: 'item';
}

/** Helper: build a clean wa.me link with a prefilled order message */
function whatsapp_link($number, $message) {
    return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
}

/** Helper: escape output for safe HTML display */
function h($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}
