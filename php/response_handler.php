<?php
error_reporting(0);
class Response
{
    use Tools;

    private static $link;

    public function __construct(int $code, $data = null, bool $debug = true, $payload = null, int $expiration = 3600)
    {
        self::setHeaders($payload, $expiration);

        $status_map = [
            200 => 'successful',
            400 => 'Bad Request',
            401 => 'unauthorized request',
            403 => 'Forbidden',
            503 => 'Service Unavailable'
        ];

        $response = [];
        $response['status'] = $status_map[$code] ?? 'error';

        if ($data) {
            self::mergeDataIntoResponse($data, $response);
        }

        if (!$debug) {
            unset($response['debug']);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function setHeaders($payload, $expiration): void
    {
        header('Content-Type: application/json');
        // header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        // header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('X-Frame-Options: SAMEORIGIN');
        header("Content-Security-Policy: default-src 'self';");
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Connection: keep-alive');

        $token = self::buildToken($payload, $expiration);
        if ($token) {
            header('bearer: ' . $token);
        }
    }

    private static function mergeDataIntoResponse(array $data, array &$response): void
    {
        foreach ($data as $key => $value) {
            $response[$key] = $value;
        }
    }

    private static function buildToken($customPayload, int $expiration): string
    {
        self::$link = self::connectToDB();
        $link = self::$link;

        $payload = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'exp' => time() + $expiration,
        ];

        if (self::loginChecker($link)) {
            $bearer = self::Headers();
            $payload['user'] = $bearer['user'] ?? null;
            $payload['token'] = $bearer['token'] ?? null;
            if (isset($bearer['key'])) {
                $payload['key'] = $bearer['key'];
            }
        }

        if (!empty($customPayload) && is_array($customPayload)) {
            foreach ($customPayload as $key => $value) {
                $payload[$key] = $value === null ? 'null' : $value;
            }
        }

        return self::createJwt($payload);
    }
    public function __destruct()
    {
        if (self::$link) {
            self::$link->close();
        }
    }
}
