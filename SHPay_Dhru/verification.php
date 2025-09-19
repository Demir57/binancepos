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
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set("Asia/Dhaka");
define("DEFINE_MY_ACCESS", true);
define("DEFINE_DHRU_FILE", true);
define("ROOTDIR", __DIR__);
include ROOTDIR . "/comm.php";
require ROOTDIR . "/includes/fun.inc.php";
include ROOTDIR . "/includes/gateway.fun.php";
$GATEWAY = loadGatewayModule("vexopayment");
if (!$GATEWAY || $GATEWAY["active"] != 1) {
    echo json_encode(["is_success" => false, "code" => 403, "message" => "Module Not Activated"]);
    exit;
}

// Rate Limiting sistemi - Dhru Fusion uyumlu
function checkRateLimit($identifier, $maxRequests = 200, $timeWindow = 3600) {
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

// Session başlat (Dhru Fusion uyumlu)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rate Limiting kontrolü
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($clientIP, 200, 3600)) { // 200 istek/saat
    echo json_encode(["is_success" => false, "code" => 429, "message" => "Rate limit exceeded. Please try again later."]);
    exit;
}
$rawBody = file_get_contents("php://input");
$input = json_decode($rawBody, true);
if (json_last_error() !== JSON_ERROR_NONE || !(isset($input["invoice_id"]) && isset($input["payment_note"]) && isset($input["expected_amount"]))) {
    echo json_encode(["is_success" => false, "code" => 400, "message" => "Invalid request data"]);
    exit;
}
$invoiceId = intval($input["invoice_id"]);
$paymentNote = trim($input["payment_note"]);
$expectedAmount = (float) $input["expected_amount"];
if ($expectedAmount <= 0 || $invoiceId <= 0 || $paymentNote === "") {
    echo json_encode(["is_success" => false, "code" => 400, "message" => "Invalid parameters"]);
    exit;
}
$apiKey = $GATEWAY["binance_api_key"];
$secretKey = $GATEWAY["binance_api_secret"];
$verification = verifyBinancePayment($apiKey, $secretKey, $expectedAmount, $paymentNote);
if (!$verification["success"]) {
    echo json_encode(["is_success" => false, "code" => 400, "message" => "Payment not found yet", "error" => $verification["error"] ?? NULL]);
    exit;
}
echo json_encode(["is_success" => true, "code" => 200, "message" => "Verification succeeded", "transaction" => $verification["transaction"], "conversion_rate" => $GATEWAY["conversion_rate"] ?? 130]);
function verifyBinancePayment($apiKey, $secretKey, $expectedAmount, $paymentNote)
{
    $baseUrl = "https://api.binance.com";
    $endpoint = "/sapi/v1/pay/transactions";
    $timestamp = round(microtime(true) * 1000);
    $params = ["timestamp" => $timestamp];
    $queryString = http_build_query($params);
    $signature = hash_hmac("sha256", $queryString, $secretKey);
    $url = $baseUrl . $endpoint . "?" . $queryString . "&signature=" . $signature;
    $ch = curl_init();
    curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ["X-MBX-APIKEY: " . $apiKey], CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($response === false || $httpCode !== 200) {
        error_log("Binance API error: HTTP " . $httpCode . " - Error: " . $curlError . " - Response: " . $response);
        return ["success" => false, "error" => "HTTP_ERROR", "code" => $httpCode, "details" => $curlError];
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Binance API JSON parse error: " . json_last_error_msg());
        return ["success" => false, "error" => "JSON_PARSE_ERROR"];
    }
    if (empty($data["success"]) || !isset($data["data"]) || !is_array($data["data"])) {
        error_log("Binance API invalid response: " . json_encode($data));
        return ["success" => false, "error" => "API_ERROR"];
    }
    $delta = 0;
    foreach ($data["data"] as $transaction) {
        if (!(isset($transaction["amount"]) && isset($transaction["note"]) && isset($transaction["currency"]))) {
        } else {
            $txAmount = (float) number_format((float) $transaction["amount"], 6, ".", "");
            $expected = (float) number_format((float) $expectedAmount, 6, ".", "");
            if (abs($txAmount - $expected) < $delta && strcasecmp($transaction["note"], $paymentNote) === 0 && strtoupper($transaction["currency"]) === "USDT") {
                return ["success" => true, "transaction" => ["id" => $transaction["transactionId"], "amount" => $transaction["amount"], "currency" => $transaction["currency"], "timestamp" => $transaction["transactionTime"] ?? NULL]];
            }
        }
    }
    return ["success" => false, "error" => "PAYMENT_NOT_FOUND"];
}

?>