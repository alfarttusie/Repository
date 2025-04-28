<<<<<<< HEAD
<?php
require 'db.php';

trait Jwt
{
    use DatabaseConfig;

    private static $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    private static function base64UrlEncode($plainText)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($plainText));
    }

    private static function base64UrlDecode($encoded)
    {
        return @base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded));
    }

    public static function createJwt(array $payload, int $expiration = 3600)
    {
        $headerEncoded = self::base64UrlEncode(json_encode(self::$header));

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiration;

        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('SHA256', "$headerEncoded.$payloadEncoded", self::$secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function verifyJwt(string $jwt): array
    {
        try {
            list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);

            $signature = self::base64UrlDecode($signatureEncoded);
            $expectedSignature = hash_hmac('SHA256', "$headerEncoded.$payloadEncoded", self::$secret, true);

            if (!hash_equals($signature, $expectedSignature)) {
                throw new Exception('Invalid signature');
            }

            $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

            if (empty($payload['exp'])) {
                throw new Exception('Missing expiration time');
            }

            if ($payload['exp'] < time()) {
                throw new Exception('Token has expired');
            }

            return [
                'valid' => true,
                'payload' => $payload
            ];
        } catch (Exception $e) {
            error_log('JWT Error: ' . $e->getMessage());
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
=======
<?php
require 'db.php';

trait Jwt
{
    use DatabaseConfig;

    private static $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    private static function base64UrlEncode($plainText)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($plainText));
    }

    private static function base64UrlDecode($encoded)
    {
        return @base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded));
    }

    public static function createJwt(array $payload, int $expiration = 3600)
    {
        $headerEncoded = self::base64UrlEncode(json_encode(self::$header));

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiration;

        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('SHA256', "$headerEncoded.$payloadEncoded", self::$secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function verifyJwt(string $jwt): array
    {
        try {
            list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);

            $signature = self::base64UrlDecode($signatureEncoded);
            $expectedSignature = hash_hmac('SHA256', "$headerEncoded.$payloadEncoded", self::$secret, true);

            if (!hash_equals($signature, $expectedSignature)) {
                throw new Exception('Invalid signature');
            }

            $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

            if (empty($payload['exp'])) {
                throw new Exception('Missing expiration time');
            }

            if ($payload['exp'] < time()) {
                throw new Exception('Token has expired');
            }

            return [
                'valid' => true,
                'payload' => $payload
            ];
        } catch (Exception $e) {
            error_log('JWT Error: ' . $e->getMessage());
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
>>>>>>> 6ab2c397ca3e2e87e98273bb53cabe79fcc7f241
