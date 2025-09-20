<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/binance_verifier.php';

final class VerifyBinancePaymentTest extends TestCase
{
    public function testReturnsErrorWhenHttpRequestFails(): void
    {
        $httpClient = static function (string $url, array $headers): array {
            return [
                'body' => false,
                'httpCode' => 500,
                'error' => 'timeout',
            ];
        };

        $result = verifyBinancePayment('api-key', 'secret', 10.0, 'Payment for #123', $httpClient);

        $this->assertFalse($result['success']);
        $this->assertSame('HTTP_ERROR', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('timeout', $result['details']);
    }

    public function testReturnsErrorWhenJsonIsInvalid(): void
    {
        $httpClient = static function (string $url, array $headers): array {
            return [
                'body' => '{invalid-json',
                'httpCode' => 200,
                'error' => '',
            ];
        };

        $result = verifyBinancePayment('api-key', 'secret', 10.0, 'Payment for #123', $httpClient);

        $this->assertFalse($result['success']);
        $this->assertSame('JSON_PARSE_ERROR', $result['error']);
    }

    public function testReturnsErrorWhenApiResponseIsMissingData(): void
    {
        $httpClient = static function (string $url, array $headers): array {
            return [
                'body' => json_encode(['success' => true, 'data' => null]),
                'httpCode' => 200,
                'error' => '',
            ];
        };

        $result = verifyBinancePayment('api-key', 'secret', 10.0, 'Payment for #123', $httpClient);

        $this->assertFalse($result['success']);
        $this->assertSame('API_ERROR', $result['error']);
    }

    public function testReturnsPaymentNotFoundWhenNoTransactionMatches(): void
    {
        $httpClient = static function (string $url, array $headers): array {
            return [
                'body' => json_encode([
                    'success' => true,
                    'data' => [
                        ['amount' => '12.000000', 'note' => 'Different note', 'currency' => 'USDT'],
                    ],
                ]),
                'httpCode' => 200,
                'error' => '',
            ];
        };

        $result = verifyBinancePayment('api-key', 'secret', 10.0, 'Payment for #123', $httpClient);

        $this->assertFalse($result['success']);
        $this->assertSame('PAYMENT_NOT_FOUND', $result['error']);
    }

    public function testReturnsSuccessWhenTransactionMatchesWithinTolerance(): void
    {
        $captured = [
            'url' => null,
            'headers' => [],
        ];

        $httpClient = function (string $url, array $headers) use (&$captured): array {
            $captured['url'] = $url;
            $captured['headers'] = $headers;

            return [
                'body' => json_encode([
                    'success' => true,
                    'data' => [
                        [
                            'transactionId' => 'abc123',
                            'amount' => '10.0000005',
                            'note' => 'Payment for #123',
                            'currency' => 'usdt',
                            'transactionTime' => 1700000000,
                        ],
                    ],
                ]),
                'httpCode' => 200,
                'error' => '',
            ];
        };

        $result = verifyBinancePayment('api-key', 'secret', 10.0, 'Payment for #123', $httpClient);

        $this->assertTrue($result['success']);
        $this->assertSame('abc123', $result['transaction']['id']);
        $this->assertSame('10.0000005', $result['transaction']['amount']);
        $this->assertSame('usdt', $result['transaction']['currency']);
        $this->assertSame(1700000000, $result['transaction']['timestamp']);

        $this->assertNotNull($captured['url']);
        $this->assertStringContainsString('signature=', $captured['url']);
        $this->assertStringContainsString('timestamp=', $captured['url']);
        $this->assertContains('X-MBX-APIKEY: api-key', $captured['headers']);
    }
}
