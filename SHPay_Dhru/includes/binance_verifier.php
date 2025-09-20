<?php

declare(strict_types=1);

if (!function_exists('verifyBinancePayment')) {
    /**
     * Verify Binance Pay transactions for a specific expected amount and note.
     *
     * @param string        $apiKey       Binance API key used for authentication.
     * @param string        $secretKey    Binance API secret used to sign the request.
     * @param float         $expectedAmount The expected transaction amount in USDT.
     * @param string        $paymentNote  The note that should match the transaction description.
     * @param callable|null $httpClient   Optional HTTP client override for testing. The callback should
     *                                     accept the request URL and headers array and return an associative
     *                                     array containing the keys `body`, `httpCode`, and `error`.
     * @param float         $tolerance    Allowed absolute difference when comparing floating point amounts.
     *
     * @return array{success:bool, transaction?:array{id:mixed, amount:mixed, currency:mixed, timestamp:mixed}, error?:string, code?:int, details?:string}
     */
    function verifyBinancePayment(
        string $apiKey,
        string $secretKey,
        float $expectedAmount,
        string $paymentNote,
        ?callable $httpClient = null,
        float $tolerance = 0.000001
    ): array {
        $baseUrl = 'https://api.binance.com';
        $endpoint = '/sapi/v1/pay/transactions';
        $timestamp = (int) round(microtime(true) * 1000);
        $params = ['timestamp' => $timestamp];
        $queryString = http_build_query($params);
        $signature = hash_hmac('sha256', $queryString, $secretKey);
        $url = $baseUrl . $endpoint . '?' . $queryString . '&signature=' . $signature;
        $headers = ['X-MBX-APIKEY: ' . $apiKey];

        if ($httpClient === null) {
            $httpClient = static function (string $requestUrl, array $requestHeaders): array {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $requestUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => $requestHeaders,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ]);

                $body = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                return [
                    'body' => $body,
                    'httpCode' => $httpCode,
                    'error' => $curlError,
                ];
            };
        }

        $clientResponse = $httpClient($url, $headers);
        $body = $clientResponse['body'] ?? false;
        $httpCode = (int) ($clientResponse['httpCode'] ?? 0);
        $error = (string) ($clientResponse['error'] ?? '');

        if ($body === false || $httpCode !== 200) {
            $responseForLog = is_string($body) ? $body : var_export($body, true);
            error_log('Binance API error: HTTP ' . $httpCode . ' - Error: ' . $error . ' - Response: ' . $responseForLog);

            return [
                'success' => false,
                'error' => 'HTTP_ERROR',
                'code' => $httpCode,
                'details' => $error,
            ];
        }

        $data = json_decode((string) $body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Binance API JSON parse error: ' . json_last_error_msg());

            return [
                'success' => false,
                'error' => 'JSON_PARSE_ERROR',
            ];
        }

        if (empty($data['success']) || !isset($data['data']) || !is_array($data['data'])) {
            error_log('Binance API invalid response: ' . json_encode($data));

            return [
                'success' => false,
                'error' => 'API_ERROR',
            ];
        }

        $tolerance = max($tolerance, 0.0);
        foreach ($data['data'] as $transaction) {
            if (!isset($transaction['amount'], $transaction['note'], $transaction['currency'])) {
                continue;
            }

            $txAmount = (float) number_format((float) $transaction['amount'], 6, '.', '');
            $expected = (float) number_format($expectedAmount, 6, '.', '');

            if (
                abs($txAmount - $expected) <= $tolerance &&
                strcasecmp((string) $transaction['note'], $paymentNote) === 0 &&
                strtoupper((string) $transaction['currency']) === 'USDT'
            ) {
                return [
                    'success' => true,
                    'transaction' => [
                        'id' => $transaction['transactionId'] ?? null,
                        'amount' => $transaction['amount'],
                        'currency' => $transaction['currency'],
                        'timestamp' => $transaction['transactionTime'] ?? null,
                    ],
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'PAYMENT_NOT_FOUND',
        ];
    }
}
