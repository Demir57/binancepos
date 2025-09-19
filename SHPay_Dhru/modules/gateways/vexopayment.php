<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.4
 * @ Decoder version: 1.0.2
 * @ Release: 10/08/2022
 */

// Decoded file for php version 74.
class VexoPayment_Service
{
    private $params = [];
    private $args = [];
    public function __construct($params)
    {
        $this->params = $params;
        $conversionRate = floatval($params["conversion_rate"]);
        $amountUSDT = floatval($params["amount"]) / $conversionRate;
        $amount = number_format($amountUSDT, 6, ".", "");
        $systemUrl = rtrim($params["systemurl"], "/");
        $callbackUrl = $systemUrl . "/shpay.php?payment=" . $params["invoiceid"];
        $this->args = ["amount" => $amount, "additional_id" => $params["invoiceid"], "callback_url" => $callbackUrl, "cancel_url" => $systemUrl . "/viewinvoice.php?id=" . $params["invoiceid"], "meta_data" => ["invoice_id" => $params["invoiceid"], "expected_amount" => $amount, "conversion_rate" => $conversionRate, "binance_api_key" => $params["binance_api_key"], "binance_api_secret" => $params["binance_api_secret"]]];
    }
    public function generate_payment_form()
    {
        $systemUrl = rtrim($this->params["systemurl"], "/");
        $postUrl = $systemUrl . "/shpay.php";
        $form = "\r\n        <form id=\"vexo_payment_form\" action=\"" . $postUrl . "\" method=\"post\">\r\n            <input type=\"hidden\" name=\"amount\" value=\"" . $this->args["amount"] . "\">\r\n            <input type=\"hidden\" name=\"invoiceid\" value=\"" . $this->args["additional_id"] . "\">\r\n            <input type=\"hidden\" name=\"currency\" value=\"USDT\">\r\n            <input type=\"hidden\" name=\"customer_name\" value=\"" . $this->params["clientdetails"]["firstname"] . " " . $this->params["clientdetails"]["lastname"] . "\">\r\n            <input type=\"hidden\" name=\"email\" value=\"" . $this->params["clientdetails"]["email"] . "\">\r\n            <input type=\"hidden\" name=\"binance_api_key\" value=\"" . $this->args["meta_data"]["binance_api_key"] . "\">\r\n            <input type=\"hidden\" name=\"binance_api_secret\" value=\"" . $this->args["meta_data"]["binance_api_secret"] . "\">\r\n        </form>\r\n        <script>\r\n            document.getElementById(\"vexo_payment_form\").submit();\r\n        </script>";
        return $form;
    }
}
function vexopayment_config()
{
    return [
        "name" => ["Type" => "System", "Value" => "SHPay - Binance C2C"], 
        "license_key" => ["Name" => "License Key", "Type" => "text", "Size" => "100", "Default" => "", "Description" => "Enter your license key for SHPay"], 
        "conversion_rate" => ["Name" => "Conversion Rate (BDT to USDT)", "Type" => "text", "Size" => "10", "Default" => "1", "Description" => "Enter conversion rate from BDT to USDT (e.g., 1)"], 
        "binance_api_key" => ["Name" => "Binance API Key", "Type" => "text", "Size" => "50", "Default" => "", "Description" => "Enter your Binance API Key"], 
        "binance_api_secret" => ["Name" => "Binance API Secret", "Type" => "text", "Size" => "50", "Default" => "", "Description" => "Enter your Binance API Secret"],
        "qr_code_path" => ["Name" => "QR Code Path", "Type" => "text", "Size" => "100", "Default" => "", "Description" => "Enter path to QR code image (e.g., /qr.png, /images/binance_qr.jpg)"],
        "gateway_icon" => ["Name" => "Gateway Icon", "Type" => "text", "Size" => "100", "Default" => "", "Description" => "Enter path to gateway icon image"],
        "gateway_logo" => ["Name" => "Gateway Logo", "Type" => "text", "Size" => "100", "Default" => "", "Description" => "Enter path to gateway logo image"]
    ];
}
function vexopayment_link($params)
{
    if (empty($params["conversion_rate"])) {
        return "<p style=\"color:red;\">Vexo Payment configuration is incomplete. Please set the conversion rate.</p>";
    }
    if (empty($params["binance_api_key"]) || empty($params["binance_api_secret"])) {
        return "<p style=\"color:red;\">Vexo Payment configuration is incomplete. Please set Binance API credentials.</p>";
    }
    $client = new vexopayment_Service($params);
    $conversionRate = floatval($params["conversion_rate"]);
    $amountBDT = floatval($params["amount"]);
    $amountUSDT = $amountBDT / $conversionRate;
    return "\r\n        <div class=\"alert alert-info\">\r\n            <strong>Amount in BDT:</strong> " . number_format($amountBDT, 2) . "৳<br>\r\n            <strong>Converted to USDT:</strong> " . number_format($amountUSDT, 6) . " USDT (Rate: 1 USDT = " . $conversionRate . "৳)\r\n        </div>\r\n        " . $client->generate_payment_form();
}

?>