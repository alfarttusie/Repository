<?php
error_reporting(0);
class Response
{
    use Tools;
    private static $debug = true;
    private static $payload = null;
    private static $data = null;
    private static function additional_data(&$response)
    {
        $data = self::$data;
        header("Bearer:" . self::Header());
        if ($data !== null) {
            foreach ($data as $key => &$value) {
                $value = empty($value) ? 'empty' : $value;
                $response[$key] = $value;
            }
        }
    }
    private static function Header()
    {
        $payload = ['ip' => $_SERVER['REMOTE_ADDR'], 'browser' => $_SERVER['HTTP_USER_AGENT'], 'exp' => time() + 3600];
        if (self::loginChecker()) {
            $Bearer = self::Headers();
            $payload['user'] = $Bearer['user'];
            $payload['token'] = $Bearer['token'];
            if (isset($Bearer['key'])) {
                $payload['key'] = $Bearer['key'];
            }
        }
        if (self::$payload != null) {
            foreach (self::$payload as $key => &$value) {
                $value = empty($value) ? 'empty' : $value;
                $payload[$key] = $value;
            }
        }
        return self::createJwt($payload);
    }
    public function __construct(int $code, array $data = null, $debug = false, array $payload = null, $link = null)
    {
        self::$data = $data;
        self::$debug = ($debug) ? true : self::$debug;
        self::$payload = $payload;
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('X-Frame-Options: SAMEORIGIN');
        header("Content-Security-Policy: default-src 'self';");
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Connection: keep-alive');
        switch ($code) {
            case 200:
                self::successful();
                return;
            case 400:
                self::BadRequest();
                return;
            case 403:
                self::forbidden();
                return;
            case 500:
                self::ServerError();
                return;
            default:
                self::ServerError();
                return;
        }
    }
    private static function successful()
    {
        http_response_code(200);
        $response['status'] = 'successful';
        self::additional_data($response);
        print_r(json_encode($response));
        return;
    }
    private static function BadRequest()
    {
        http_response_code(400);
        $response['status'] = 'Bad Request';
        self::additional_data($response);
        if (!self::$debug) {
            unset($response['debug']);
        }

        print_r(json_encode($response));
        return;
    }
    private static function forbidden()
    {
        http_response_code(403);
        $response['status'] = 'Forbidden';
        self::additional_data($response);
        if (!self::$debug) {
            unset($response['debug']);
        }

        print_r(json_encode($response));
        return;
    }
    private static function ServerError()
    {
        http_response_code(500);
        $response['status'] = 'error';
        self::additional_data($response);
        print_r(json_encode($response));
        return;
    }
}
