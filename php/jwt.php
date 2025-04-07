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

    private static function createJwt($payload)
    {
        $headerEncoded = self::base64UrlEncode(json_encode(self::$header));
        $payload['iat'] = time();
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('SHA256', "$headerEncoded.$payloadEncoded", self::$secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    private static function verifyJwt(&$jwt)
    {
        try {
            @list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
            $signature = self::base64UrlDecode($signatureEncoded);
            $expectedSignature = hash_hmac('SHA256', "$headerEncoded.$payloadEncoded", self::$secret, true);
            $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
            switch (true) {
                case !hash_equals($signature, $expectedSignature):
                    throw new Exception('Invalid signature');
                    break;
                case empty($payload['exp']):
                    throw new Exception('Missing expiration time');
                    break;
                case $payload['exp'] < time():
                    throw new Exception('Token has expired');
                    break;
            }
            $jwt = $payload;
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
