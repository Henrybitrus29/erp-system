<?php
/**
 * Sends a stock alert payload to Make.com Webhook
 *
 * @param string $product_name
 * @param int $current_stock
 * @param string $alert_type
 * @return bool
 */
function sendMakeWebhookAlert($product_name, $current_stock, $alert_type = 'LOW_STOCK') {
    $webhook_url = 'https://hook.eu1.make.com/d858n8olj9732sfpbg213vua6qoqscml';

    $payload = [
        'event'         => 'stock_alert',
        'product_name'  => $product_name,
        'current_stock' => (int)$current_stock,
        'alert_type'    => $alert_type,
        'timestamp'     => date('Y-m-d H:i:s')
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code === 200 || $response === 'Accepted');
}

// Example usage inside your script:
// sendMakeWebhookAlert('DELL LAPTOP', 4, 'URGENT_RESTOCK');
?>