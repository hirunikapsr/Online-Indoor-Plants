<?php
// includes/payhere_config.php - PayHere Payment Gateway Configuration & Helpers

// 1. PayHere Mode: Set to true for Sandbox (Testing), false for Live (Production)
if (!defined('PAYHERE_SANDBOX')) {
    define('PAYHERE_SANDBOX', true);
}

// 2. PayHere Merchant Credentials
// Merchant ID (from PayHere Dashboard -> Integrations -> Domains & Credentials)
if (!defined('PAYHERE_MERCHANT_ID')) {
    define('PAYHERE_MERCHANT_ID', '1238520');
}

// Merchant Secret (Provided by PayHere)
if (!defined('PAYHERE_MERCHANT_SECRET')) {
    define('PAYHERE_MERCHANT_SECRET', 'MjQwNDQ3MTM1ODM1NTg4OTMzNzMyNjQ3MTE0OTE2MzYzNjk2MTg4NA==');
}

// 3. Currency
if (!defined('PAYHERE_CURRENCY')) {
    define('PAYHERE_CURRENCY', 'LKR');
}

// 4. PayHere Gateway URLs
if (PAYHERE_SANDBOX) {
    define('PAYHERE_CHECKOUT_URL', 'https://sandbox.payhere.lk/pay/checkout');
} else {
    define('PAYHERE_CHECKOUT_URL', 'https://www.payhere.lk/pay/checkout');
}

/**
 * Generate PayHere MD5 Checkout Hash
 * Formula: strtoupper(md5(merchant_id + order_id + amount + currency + strtoupper(md5(merchant_secret))))
 */
function payhere_generate_hash($order_id, $amount, $currency = PAYHERE_CURRENCY, $merchant_id = PAYHERE_MERCHANT_ID, $merchant_secret = PAYHERE_MERCHANT_SECRET) {
    $formatted_amount = number_format((float)$amount, 2, '.', '');
    $hashed_secret = strtoupper(md5($merchant_secret));
    return strtoupper(md5($merchant_id . $order_id . $formatted_amount . $currency . $hashed_secret));
}

/**
 * Verify PayHere IPN Signature
 * Formula: strtoupper(md5(merchant_id + order_id + payhere_amount + payhere_currency + status_code + strtoupper(md5(merchant_secret))))
 */
function payhere_verify_ipn_signature($merchant_id, $order_id, $payhere_amount, $payhere_currency, $status_code, $md5sig, $merchant_secret = PAYHERE_MERCHANT_SECRET) {
    $hashed_secret = strtoupper(md5($merchant_secret));
    $local_md5sig = strtoupper(md5($merchant_id . $order_id . $payhere_amount . $payhere_currency . $status_code . $hashed_secret));
    return ($local_md5sig === strtoupper($md5sig));
}

/**
 * Get dynamic base URL for the application (works on localhost or live domain)
 */
function payhere_get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base_dir = preg_replace('#/(pages|api|includes|admin)$#i', '', $script_dir);
    if ($base_dir === '/' || empty($base_dir)) {
        $base_dir = '';
    }
    return rtrim($protocol . $host . $base_dir, '/');
}
