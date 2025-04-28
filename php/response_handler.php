<<<<<<< HEAD
<?php

// اضبط عرض الأخطاء بناءً على البيئة (تعطيل الأخطاء في الإنتاج)
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

class Response
{
    use Tools;

    public function __construct(int $code, $data = null, bool $debug = false, $payload = null)
    {
        self::setHeaders();

        $response = [];

        $status_map = [
            200 => 'successful',
            400 => 'Bad Request',
            401 => 'unauthorized request',
            403 => 'Forbidden',
            503 => 'Service Unavailable',
            500 => 'Server Error'
        ];

        $response['status'] = $status_map[$code] ?? 'error';

        if ($data) {
            self::additional_data($data, $response);
        }

        if (!$debug)
            unset($response['debug']);

        echo json_encode($response);
    }

    private static function setHeaders(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('X-Frame-Options: SAMEORIGIN');
        header("Content-Security-Policy: default-src 'self';");
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Connection: keep-alive');

        $token = self::generateToken();
        header('Authorization: Bearer ' . $token);
    }

    private static function additional_data(array $data, array &$response): void
    {
        foreach ($data as $key => $value) {
            $response[$key] = $value;
        }
    }
    private static function generateToken(): string
    {
        // $payload = [
        //     'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        //     'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        //     'exp' => time() + 3600,
        // ];

        // if ($this->loginChecker()) {
        //     $bearer = $this->Headers();
        //     $payload['user'] = $bearer['user'] ?? null;
        //     $payload['token'] = $bearer['token'] ?? null;
        //     if (isset($bearer['key'])) {
        //         $payload['key'] = $bearer['key'];
        //     }
        // }

        // if (!empty($this->payload)) {
        //     foreach ($this->payload as $key => $value) {
        //         $payload[$key] = ($value === null) ? 'null' : $value;
        //     }
        // }

        // return $this->createJwt($payload);
        return "sample_token";
    }
}
=======
<?php

// اضبط عرض الأخطاء بناءً على البيئة (تعطيل الأخطاء في الإنتاج)
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

class Response
{
    use Tools;

    private $debug;
    private $payload;
    private $data;

    public function __construct(int $code, $data = null, bool $debug = false, $payload = null)
    {
        $this->data = $data;
        $this->debug = $debug;
        $this->payload = $payload;

        $this->setHeaders();
        $this->handleResponse($code);
    }

    private function setHeaders(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('X-Frame-Options: SAMEORIGIN');
        header("Content-Security-Policy: default-src 'self';");
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Connection: keep-alive');

        // اضافة الهيدر الخاص بالتوكن
        $token = $this->generateToken();
        header('Authorization: Bearer ' . $token);
    }

    private function generateToken(): string
    {
        $payload = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'exp' => time() + 3600,
        ];

        if ($this->loginChecker()) {
            $bearer = $this->Headers();
            $payload['user'] = $bearer['user'] ?? null;
            $payload['token'] = $bearer['token'] ?? null;
            if (isset($bearer['key'])) {
                $payload['key'] = $bearer['key'];
            }
        }

        if (!empty($this->payload)) {
            foreach ($this->payload as $key => $value) {
                $payload[$key] = ($value === null) ? 'null' : $value;
            }
        }

        return $this->createJwt($payload);
    }

    private function handleResponse(int $code): void
    {
        switch ($code) {
            case 200:
                $this->sendResponse(200, 'successful');
                break;
            case 400:
                $this->sendResponse(400, 'Bad Request');
                break;
            case 403:
                $this->sendResponse(403, 'Forbidden');
                break;
            case 500:
            default:
                $this->sendResponse(500, 'Server Error');
                break;
        }
    }

    private function sendResponse(int $httpCode, string $status): void
    {
        http_response_code($httpCode);

        $response = ['status' => $status];

        if (!empty($this->data)) {
            foreach ($this->data as $key => $value) {
                $response[$key] = ($value === null) ? 'null' : $value;
            }
        }

        if (!$this->debug) {
            unset($response['debug']);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
>>>>>>> 6ab2c397ca3e2e87e98273bb53cabe79fcc7f241
