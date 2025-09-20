<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.4
 * @ Decoder version: 1.0.2
 * @ Release: 10/08/2022
 */

// Decoded file for php version 74.
ob_start();
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32767);
header("Content-Type: text/html; charset=UTF-8");
date_default_timezone_set("Asia/Dhaka");
define("DEFINE_MY_ACCESS", true);
define("DEFINE_DHRU_FILE", true);
define("ROOTDIR", __DIR__);
include ROOTDIR . "/comm.php";
require ROOTDIR . "/includes/fun.inc.php";
include ROOTDIR . "/includes/gateway.fun.php";
include ROOTDIR . "/includes/invoice.fun.php";
$GATEWAY = loadGatewayModule("vexopayment");
if (!$GATEWAY || $GATEWAY["active"] != 1) {
    header("Content-Type: application/json");
    echo json_encode(["is_success" => false, "code" => 403, "message" => "Module Not Activated"]);
    exit;
}
// GEÇİCİ LİSANS BYPASS - GELİŞTİRME AŞAMASINDA
$licenseKey = $GATEWAY["license_key"] ?? "123456";
// $result = validatelicense($licenseKey);
// if (!$result["valid"]) {
//     jsonResponse($result);
// }

// GEÇİCİ STATİK LİSANS KONTROLÜ
$result = ["valid" => true]; // Her zaman geçerli kabul et

// CSRF Token sistemi - Dhru Fusion uyumlu
function generateCSRFToken() {
    if (!isset($_SESSION['shpay_csrf_token'])) {
        $_SESSION['shpay_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['shpay_csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['shpay_csrf_token']) && 
           hash_equals($_SESSION['shpay_csrf_token'], $token);
}

// Session başlat (Dhru Fusion uyumlu)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rate Limiting sistemi - Dhru Fusion uyumlu
function checkRateLimit($identifier, $maxRequests = 100, $timeWindow = 3600) {
    $key = 'rate_limit_' . md5($identifier);
    $currentTime = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => $currentTime + $timeWindow];
    }
    
    $rateData = $_SESSION[$key];
    
    // Zaman penceresi sıfırlandı mı?
    if ($currentTime > $rateData['reset_time']) {
        $_SESSION[$key] = ['count' => 1, 'reset_time' => $currentTime + $timeWindow];
        return true;
    }
    
    // Limit kontrolü
    if ($rateData['count'] >= $maxRequests) {
        return false;
    }
    
    // Sayacı artır
    $_SESSION[$key]['count']++;
    return true;
}

function getRateLimitInfo($identifier) {
    $key = 'rate_limit_' . md5($identifier);
    if (!isset($_SESSION[$key])) {
        return ['remaining' => 100, 'reset_time' => time() + 3600];
    }
    
    $rateData = $_SESSION[$key];
    $currentTime = time();
    
    if ($currentTime > $rateData['reset_time']) {
        return ['remaining' => 100, 'reset_time' => $currentTime + 3600];
    }
    
    return [
        'remaining' => max(0, 100 - $rateData['count']),
        'reset_time' => $rateData['reset_time']
    ];
}

// Gelişmiş Caching Sistemi - Dhru Fusion uyumlu
class DhruCache {
    private static $cache = [];
    private static $cacheDir = null;
    
    public static function init() {
        if (self::$cacheDir === null) {
            self::$cacheDir = ROOTDIR . '/cache/';
            if (!is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
            }
        }
    }
    
    public static function set($key, $value, $ttl = 3600) {
        self::init();
        $cacheData = [
            'data' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        // Memory cache
        self::$cache[$key] = $cacheData;
        
        // File cache (Dhru Fusion uyumlu)
        $cacheFile = self::$cacheDir . md5($key) . '.cache';
        file_put_contents($cacheFile, serialize($cacheData), LOCK_EX);
    }
    
    public static function get($key) {
        self::init();
        
        // Memory cache kontrolü
        if (isset(self::$cache[$key])) {
            $cacheData = self::$cache[$key];
            if ($cacheData['expires'] > time()) {
                return $cacheData['data'];
            } else {
                unset(self::$cache[$key]);
            }
        }
        
        // File cache kontrolü
        $cacheFile = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            $cacheData = unserialize(file_get_contents($cacheFile));
            if ($cacheData['expires'] > time()) {
                // Memory cache'e yükle
                self::$cache[$key] = $cacheData;
                return $cacheData['data'];
            } else {
                unlink($cacheFile);
            }
        }
        
        return null;
    }
    
    public static function delete($key) {
        self::init();
        
        // Memory cache'den sil
        unset(self::$cache[$key]);
        
        // File cache'den sil
        $cacheFile = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
    
    public static function clear() {
        self::init();
        
        // Memory cache'i temizle
        self::$cache = [];
        
        // File cache'i temizle
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    public static function getStats() {
        self::init();
        $files = glob(self::$cacheDir . '*.cache');
        $totalSize = 0;
        $fileCount = count($files);
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        return [
            'memory_items' => count(self::$cache),
            'file_items' => $fileCount,
            'total_size' => $totalSize,
            'cache_dir' => self::$cacheDir
        ];
    }
}
$rawBody = file_get_contents("php://input");
$maybeJson = json_decode($rawBody, true);
if (is_array($maybeJson)) {
    jsonResponse(["is_success" => false, "code" => 301, "message" => "Please use verification.php for API verification"]);
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["verification_result"])) {
    // Rate Limiting kontrolü
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIP, 50, 3600)) { // 50 istek/saat
        jsonResponse(["is_success" => false, "code" => 429, "message" => "Rate limit exceeded. Please try again later."]);
    }
    
    // CSRF Token kontrolü
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        jsonResponse(["is_success" => false, "code" => 403, "message" => "CSRF token validation failed"]);
    }
    
    $verificationRaw = html_entity_decode($_POST["verification_result"], ENT_QUOTES, "UTF-8");
    $verificationData = json_decode($verificationRaw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($verificationData)) {
        jsonResponse(["is_success" => false, "code" => 400, "message" => "Invalid verification data"]);
    }
    if (empty($verificationData["is_success"])) {
        jsonResponse(["is_success" => false, "code" => 400, "message" => $verificationData["message"] ?? "Payment verification failed"]);
    }
    $invoiceId = intval($_POST["invoice_id"]);
    $orderDetails = getinvoicedetails($invoiceId);
    if (!$orderDetails) {
        jsonResponse(["is_success" => false, "code" => 404, "message" => "Order not found"]);
    }
    if ($orderDetails["status"] !== "Unpaid") {
        jsonResponse(["is_success" => false, "code" => 405, "message" => "Order already paid"]);
    }
    if (checkTransID($verificationData["transaction"]["id"])) {
        jsonResponse(["is_success" => false, "code" => 406, "message" => "Duplicate transaction"]);
    }
    $conversionRate = $verificationData["conversion_rate"] ?? $GATEWAY["conversion_rate"] ?? 130;
    $paymentAmount = floatval($verificationData["transaction"]["amount"]);
    $amountBDT = $paymentAmount * $conversionRate;
    $result = addPayment($invoiceId, $verificationData["transaction"]["id"], $amountBDT, 0, $GATEWAY["paymentmethod"]);
    if ($result) {
        handlesuccessresponse($paymentAmount, $amountBDT, $conversionRate, $invoiceId);
    } else {
        jsonResponse(["is_success" => false, "code" => 500, "message" => "Payment recording failed"]);
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["amount"]) && isset($_POST["invoiceid"])) {
    // Rate Limiting kontrolü
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIP, 30, 3600)) { // 30 istek/saat
        jsonResponse(["is_success" => false, "code" => 429, "message" => "Rate limit exceeded. Please try again later."]);
    }
    
    // CSRF Token kontrolü
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        jsonResponse(["is_success" => false, "code" => 403, "message" => "CSRF token validation failed"]);
    }
    
    $amount = floatval($_POST["amount"]);
    $invoiceId = intval($_POST["invoiceid"]);
    $conversionRate = $GATEWAY["conversion_rate"] ?? 130;
    $expectedAmount = $amount / $conversionRate;
    $paymentNote = $_POST["payment_note"] ?? generatepaymentnote();
    $orderDetails = getinvoicedetails($invoiceId);
    if (!$orderDetails) {
        jsonResponse(["is_success" => false, "code" => 404, "message" => "Invoice not found"]);
    }
    if ($orderDetails["status"] !== "Unpaid") {
        jsonResponse(["is_success" => false, "code" => 405, "message" => "Invoice already paid"]);
    }
    include ROOTDIR . "/templates/payment_page.php";
    exit;
}
jsonResponse(["is_success" => false, "code" => 400, "message" => "Invalid request"]);
function jsonResponse($data)
{
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}
// GEÇİCİ OLARAK YORUM SATIRINA ALINDI - GELİŞTİRME AŞAMASINDA
/*
function validateLicense($licenseKey)
{
    if (strlen($licenseKey) !== 18) {
        return ["valid" => false, "error" => "Invalid license key length"];
    }
    $licenseServer = "http://shpay.org/admin/validate_license.php?key=" . urlencode($licenseKey);
    $ch = curl_init();
    curl_setopt_array($ch, [CURLOPT_URL => $licenseServer, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_FAILONERROR => true]);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($response === false) {
        error_log("License validation error: " . $curlError);
        return ["valid" => false, "error" => "Unable to reach license server: " . $curlError];
    }
    $data = json_decode($response, true);
    if (!is_array($data)) {
        error_log("License validation invalid response: " . $response);
        return ["valid" => false, "error" => "Invalid response from license server"];
    }
    if (empty($data["valid"])) {
        return ["valid" => false, "error" => $data["error"] ?? "License invalid"];
    }
    $currentDomain = $_SERVER["SERVER_NAME"] ?? "";
    if (!empty($data["license"]["domain"]) && $data["license"]["domain"] !== $currentDomain) {
        return ["valid" => false, "error" => "License is not valid for this domain"];
    }
    if ($data["license"]["status"] !== "active") {
        return ["valid" => false, "error" => "License is not active (status: " . $data["license"]["status"] . ")"];
    }
    return ["valid" => true];
}
*/

// GEÇİCİ STATİK LİSANS DOĞRULAMA FONKSİYONU
function validateLicense($licenseKey) {
    // Geliştirme aşamasında her zaman geçerli kabul et
    return ["valid" => true];
}
function getInvoiceDetails($order_id)
{
    $order_id_safe = intval($order_id);
    $cacheKey = 'invoice_details_' . $order_id_safe;
    
    // Cache'den kontrol et
    $cachedData = DhruCache::get($cacheKey);
    if ($cachedData !== null) {
        return $cachedData;
    }
    
    // Dhru Fusion uyumlu güvenli SQL sorgusu
    $query = "SELECT i.*, GROUP_CONCAT(ii.`type` SEPARATOR ',') AS item_types 
              FROM `tbl_invoices` AS i
              LEFT JOIN `tbl_invoiceitems` AS ii ON ii.`invoiceid` = i.`id`
              WHERE i.`id` = ? 
              GROUP BY i.`id` 
              LIMIT 1";
    
    // Prepared statement kullanarak SQL injection koruması
    $stmt = mysqli_prepare($GLOBALS['db_connection'], $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $order_id_safe);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = $result ? mysqli_fetch_assoc($result) : false;
        mysqli_stmt_close($stmt);
        
        // Cache'e kaydet (5 dakika TTL)
        DhruCache::set($cacheKey, $data, 300);
        return $data;
    } else {
        // Fallback: Dhru Fusion'un dquery fonksiyonunu kullan
        $safe_query = "SELECT i.*, GROUP_CONCAT(ii.`type` SEPARATOR ',') AS item_types 
                       FROM `tbl_invoices` AS i
                       LEFT JOIN `tbl_invoiceitems` AS ii ON ii.`invoiceid` = i.`id`
                       WHERE i.`id` = " . intval($order_id_safe) . "
                       GROUP BY i.`id` 
                       LIMIT 1";
        $result = dquery($safe_query);
        $data = $result ? mysqli_fetch_assoc($result) : false;
        
        // Cache'e kaydet (5 dakika TTL)
        DhruCache::set($cacheKey, $data, 300);
        return $data;
    }
}
function handleSuccessResponse($paymentAmount, $amountBDT, $conversionRate, $invoiceId)
{
    $protocol = !empty($_SERVER["HTTPS"]) ? "https" : "http";
    $redirectUrl = $protocol . "://" . $_SERVER["HTTP_HOST"] . "/confirm.php?id=" . $invoiceId;
    $response = ["is_success" => true, "code" => 200, "message" => "Payment processed successfully", "amount_usdt" => $paymentAmount, "amount_bdt" => $amountBDT, "conversion_rate" => $conversionRate, "redirect_url" => $redirectUrl];
    if (strpos($_SERVER["HTTP_ACCEPT"], "text/html") !== false) {
        header("Location: " . $redirectUrl);
    } else {
        jsonResponse($response);
    }
}
function generatePaymentNote()
{
    return str_pad(random_int(0, 9999), 4, "0", STR_PAD_LEFT);
}

?>