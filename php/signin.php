<?php

class Signin
{
    use Tools;

    private static $link;
    private static $userIp;
    function __construct($Post, $link)
    {
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            session_regenerate_id(true);

            $username = $Post['username'] ?? null;
            $password = $Post['password'] ?? null;

            if (empty($username) && !empty($password)) return new Response(400, ['debug' => 'empty']);

            $username = base64_decode($Post['username']) ?? 'empty';
            $password = base64_decode($Post['password']) ?? 'empty';
            self::$userIp = mysqli_real_escape_string($link, $_SERVER['REMOTE_ADDR']);
            $time = NULL;
            if (self::isUserBlocked($time, $link))  return new Response(200, ['response' => 'blocked', 'time' => $time]);


            if (self::validateCredentials($username, $password, $link))
                return new Response(200,  ['response' => 'ok'], payload: ['user' => $username] + self::generateAuthToken($username, $link));
            else
                return new Response(200, ['response' => 'wrong', 'attemptsLeft' => self::recordFailedLogin($link)]);
        } catch (Exception $exception) {
            return new Response(403, ['debug' => $exception->getMessage()]);
        }
    }
    private static function isUserBlocked(&$time, $link)
    {

        $stmtVisitor   = $link->prepare("SELECT * FROM `visitors` WHERE `ip` = ?");
        $stmtVisitor->bind_param("s", self::$userIp);
        $stmtVisitor->execute();
        $visitor       = $stmtVisitor->get_result()->fetch_assoc();

        if (empty($visitor)) {
            $stmtInsert = $link->prepare("INSERT INTO `visitors` (`ip`, `times`, `time`) VALUES (?, 1, ?)");
            $currentTime = time();
            $stmtInsert->bind_param("si", self::$userIp, $currentTime);
            $stmtInsert->execute();
            return false;
        };

        $stmtSetting = $link->prepare("SELECT * FROM `setting` LIMIT 1");
        $stmtSetting->execute();
        $setting = $stmtSetting->get_result()->fetch_assoc();

        if ($setting['times'] > $visitor['times']) return false;

        if ($visitor['times'] >= $setting['times']) {
            if ($visitor['time'] <=  time()) {
                $stmtUpdate = $link->prepare("UPDATE `visitors` SET `times` = '0' WHERE `ip` = ?");
                $stmtUpdate->bind_param("s", self::$userIp);
                $stmtUpdate->execute();
                return false;
            } else {
                $time = intval($visitor['time']) - time();
                return true;
            }
        }
        return true;
    }
    private static function recordFailedLogin($link)
    {

        $stmtVisitor = $link->prepare("SELECT `times` FROM `visitors` WHERE `ip` = ?");
        $stmtVisitor->bind_param("s", self::$userIp);
        $stmtVisitor->execute();
        $visitor = $stmtVisitor->get_result()->fetch_assoc();
        $stmtSetting = $link->prepare("SELECT `times`,`time` FROM `setting` LIMIT 1");
        $stmtSetting->execute();
        $setting = $stmtSetting->get_result()->fetch_assoc();

        $times = intval($visitor['times']) + 1;

        $blockTime = $setting['time'] * 60;
        $time = time() + $blockTime;

        $stmtUpdate = $link->prepare("UPDATE `visitors` SET `times` = ?, `time` = ? WHERE `ip` = ?");
        $stmtUpdate->bind_param("iis", $times, $time, self::$userIp);
        $stmtUpdate->execute();
        $maxAttempts = $setting['times'];
        $attemptsLeft = $maxAttempts - $times;

        return $attemptsLeft == 0 ? 'none' : $attemptsLeft;
    }
    private static function validateCredentials($username, $password, $link)
    {
        $username = htmlspecialchars(trim($username), ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars(trim($password), ENT_QUOTES, 'UTF-8');
        $stmt = $link->prepare("SELECT password FROM `admin_info` WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        usleep(random_int(300000, 700000));
        return $result && password_verify($password, $result['password']);
    }
    private static function generateAuthToken($username, $link)
    {
        $encKey               = self::generateRandomString(10);
        self::$encryptionKey  = hash('sha256', $encKey, true);
        $randomToken          = bin2hex(random_bytes(20));
        $loginToken           = self::encryptText($randomToken);
        $stmtUpdate           = $link->prepare("UPDATE `admin_info` SET `Token` = ?, `enckey` = ? WHERE `username` = ?");
        $stmtUpdate->bind_param("sss", $randomToken, $encKey, $username);
        $stmtUpdate->execute();
        $_SESSION['session_token']    = ['user' => $username, 'token' => $loginToken];
        return ['token' => $loginToken];
    }
}
