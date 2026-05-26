<?php
/**
 * Stripe API helper (no SDK required)
 */

require_once __DIR__ . '/../includes/config.php';

function stripeRequest(string $endpoint, array $data, string $method = 'POST'): array
{
    $url = 'https://api.stripe.com/v1/' . ltrim($endpoint, '/');

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 30,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => ['message' => $error]];
    }

    return json_decode($response, true) ?: ['error' => ['message' => 'Invalid response']];
}

function createCheckoutSession(int $userId, float $amount, string $successUrl, string $cancelUrl): array
{
    $amountCents = (int) round($amount * 100);

    return stripeRequest('checkout/sessions', [
        'mode'                   => 'payment',
        'success_url'            => $successUrl,
        'cancel_url'             => $cancelUrl,
        'client_reference_id'    => (string) $userId,
        'line_items[0][price_data][currency]'     => STRIPE_CURRENCY,
        'line_items[0][price_data][unit_amount]'  => $amountCents,
        'line_items[0][price_data][product_data][name]' => SITE_NAME . ' — Add Funds',
        'line_items[0][quantity]'                 => 1,
        'metadata[user_id]'      => $userId,
        'metadata[amount]'       => $amount,
    ]);
}

function getCheckoutSession(string $sessionId): array
{
    return stripeRequest('checkout/sessions/' . $sessionId, [], 'GET');
}

// GET not supported well with POST-only curl above — fix getCheckoutSession
function stripeGet(string $endpoint): array
{
    $url = 'https://api.stripe.com/v1/' . ltrim($endpoint, '/');
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?: [];
}
