<?php

error_reporting(0);

class Signin
{
    use Tools;

    private static $link;
    private static $userIp;

    function __construct($Post, $link)
    {
        try {
            self::$link = $link;
            self::$userIp = $_SERVER['REMOTE_ADDR'];

            $username = $Post['username'] ?? null;
            $password = $Post['password'] ?? null;

            if (empty($username) || empty($password)) {
                return new Response(400, ['debug' => 'empty']);
            }

            $time = null;
            if (self::isUserBlocked($time)) {
                return new Response(200, ['response' => 'blocked', 'time' => $time]);
            }

            if (self::validateCredentials($username, $password)) {
                self::deleteExpiredTokens($link);
                $isRecognizedDevice = self::isRecognizedDevice($username);
                $payload = ['user' => $username] + self::generateAuthToken($username);
                return new Response(200, ['response' => 'ok', 'isRecognizedDevice' => $isRecognizedDevice], payload: $payload);
            } else {
                return new Response(200, ['response' => 'wrong', 'attemptsLeft' => self::recordFailedLogin()]);
            }
        } catch (Exception $exception) {
            return new Response(403, ['debug' => $exception->getMessage()]);
        }
    }
    private static function isUserBlocked(&$time): bool
    {
        $stmt = self::$link->prepare("SELECT `times`, `time` FROM `visitors` WHERE `ip` = ?");
        $stmt->bind_param("s", self::$userIp);
        $stmt->execute();
        $visitor = $stmt->get_result()->fetch_assoc();

        if (!$visitor) {
            return false;
        }

        $setting = self::$link->query("SELECT `times`, `time` FROM `setting` LIMIT 1")->fetch_assoc();

        if ($visitor['time'] < 1000) {
            self::$link->qery("DELETE FROM `visitors` WHERE ip = '" . self::$userIp . "'");
        }
        if ($visitor['times'] < $setting['times']) {
            return false;
        }

        if ($visitor['time'] <= time()) {
            $reset = self::$link->prepare("UPDATE `visitors` SET `times` = 0, `time` = NULL WHERE `ip` = ?");
            $reset->bind_param("s", self::$userIp);
            $reset->execute();
            return false;
        }

        $time = intval($visitor['time']) - time();
        return true;
    }
    private static function recordFailedLogin()
    {
        $stmt = self::$link->prepare("SELECT `times` FROM `visitors` WHERE `ip` = ?");
        $stmt->bind_param("s", self::$userIp);
        $stmt->execute();
        $visitor = $stmt->get_result()->fetch_assoc();

        $setting = self::$link->query("SELECT `times`, `time` FROM `setting` LIMIT 1")->fetch_assoc();
        $blockUntil = time() + ($setting['time'] * 60);

        $times = intval($visitor['times']);
        if ($times < 1) {
            $insert = self::$link->prepare("INSERT INTO `visitors` (`ip`, `times`, `time`) VALUES (?, ?, ?)");
            $initial = 1;
            $insert->bind_param("sii", self::$userIp, $initial, $blockUntil);
            $insert->execute();
            $left = $setting['times'] - 1;
        } else {
            $times = $times + 1;
            $update = self::$link->prepare("UPDATE `visitors` SET `times` = ?, `time` = ? WHERE `ip` = ?");
            $update->bind_param("iis", $times, $blockUntil, self::$userIp);
            $update->execute();
            $left = $setting['times'] - $times;
        }

        return $left <= 0 ? 'none' : $left;
    }
    private static function validateCredentials($username, $password): bool
    {
        $stmt = self::$link->prepare("SELECT password FROM `admin_info` WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) return false;

        usleep(random_int(300000, 700000));
        return password_verify($password, $result['password']);
    }
    private static function storeSessionToken($username, $token): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $created = date('Y-m-d H:i:s');
        $expiry = date('Y-m-d H:i:s', strtotime('+1 day'));

        $stmt = self::$link->prepare("
        INSERT INTO `auth_tokens` (`username`, `token`, `ip_address`, `user_agent`, `created_at`, `expiry`) 
        VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss", $username, $token, $ip, $agent, $created, $expiry);
        $stmt->execute();
    }
    private static function generateAuthToken($username): array
    {
        $token = bin2hex(random_bytes(32));
        self::storeSessionToken($username, $token);
        $domain = $_SERVER['HTTP_HOST'];
        setcookie(
            "session_token",
            json_encode(['user' => $username, 'token' => $token]),
            time() + 86400,
            "/",
            $domain,
            isset($_SERVER['HTTPS']),
            true
        );

        return ['token' => $token];
    }
    private static function isRecognizedDevice($username): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = self::$link->prepare("
        SELECT id FROM `auth_tokens` 
        WHERE `username` = ? AND `ip_address` = ? AND `user_agent` = ? 
        LIMIT 1
    ");
        $stmt->bind_param("sss", $username, $ip, $userAgent);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows === 0;
    }
}
