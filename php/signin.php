<?php

class Signin
{
    use Tools;

    private static $link;
    private static $ip;
    function __construct($Post, $link)
    {
        try {
            $username = @$Post['username'] ?? null;
            $password = @$Post['password'] ?? null;
            if (!empty($username) && !empty($password)) {
                $username = base64_decode($Post['username']);
                $password = base64_decode($Post['password']);
                $link = self::connectToDB();
                self::$ip = mysqli_real_escape_string($link, $_SERVER['REMOTE_ADDR']);
                if (self::CheckVisitorStatus($time, $link)) {
                    if (self::MatchInfo($username, $password, $link)) {
                        return new Response(200,  ['response' => 'ok'], payload: ['user' => $username] + self::CreatLogiinToken($username,$link));
                    } else
                        return new Response(200, ['response' => 'wrong', 'attemptsLeft' => self::AddLogin_attempt($link)]);
                } else {
                    return new Response(200, ['response' => 'blocked', 'time' => $time]);
                }
            } else
                return new Response(400, ['debug' => 'empty']);
        } catch (Exception $exception) {
            return new Response(403, ['debug' => $exception->getMessage()]);
        }
    }
    private static function CheckVisitorStatus(&$time, $link)
    {
        $stmtVisitor   = $link->prepare("SELECT * FROM `visitors` WHERE `ip` = ?");
        $stmtVisitor->bind_param("s", self::$ip);
        $stmtVisitor->execute();
        $visitor       = $stmtVisitor->get_result()->fetch_assoc();

        $stmtSetting = $link->prepare("SELECT * FROM `setting` LIMIT 1");
        $stmtSetting->execute();
        $setting = $stmtSetting->get_result()->fetch_assoc();
        if (!empty($visitor)) {
            if ($visitor['times'] >= $setting['times']) {
                if ($visitor['time'] <=  time()) {
                    $stmtUpdate = $link->prepare("UPDATE `visitors` SET `times` = '0' WHERE `ip` = ?");
                    $stmtUpdate->bind_param("s", self::$ip);
                    $stmtUpdate->execute();
                    return true;
                } else {
                    $time = intval($visitor['time']) - time();
                    return false;
                }
            } else {
                return true;
            }
        } else {
            $stmtInsert = $link->prepare("INSERT INTO `visitors` (`ip`, `times`, `time`) VALUES (?, 1, ?)");
            $currentTime = time();
            $stmtInsert->bind_param("si", self::$ip, $currentTime);
            $stmtInsert->execute();
            return true;
        }
    }
    private static function AddLogin_attempt($link)
    {

        $stmtVisitor = $link->prepare("SELECT `times` FROM `visitors` WHERE `ip` = ?");
        $stmtVisitor->bind_param("s", self::$ip);
        $stmtVisitor->execute();
        $visitor = $stmtVisitor->get_result()->fetch_assoc();
        $stmtSetting = $link->prepare("SELECT `times`,`time` FROM `setting` LIMIT 1");
        $stmtSetting->execute();
        $setting = $stmtSetting->get_result()->fetch_assoc();

        $times = intval($visitor['times']) + 1;

        $blockTime = $setting['time'] * 60;
        $time = time() + $blockTime;

        $stmtUpdate = $link->prepare("UPDATE `visitors` SET `times` = ?, `time` = ? WHERE `ip` = ?");
        $stmtUpdate->bind_param("iis", $times, $time, self::$ip);
        $stmtUpdate->execute();
        $maxAttempts = $setting['times'];
        $attemptsLeft = $maxAttempts - $times;

        return $attemptsLeft == 0 ? 'none' : $attemptsLeft;
    }
    private static function MatchInfo($username, $password, $link)
    {
        $stmt = $link->prepare("SELECT password FROM `admin_info` WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result && password_verify($password, $result['password']);
    }
    private static function CreatLogiinToken($username,$link)
    {
        session_start();
        $encKey               = self::generateRandomString(10);
        self::$encryptionKey  = hash('sha256', $encKey, true);
        $randomToken          = self::generateRandomString(20);
        $loginToken           = self::encryptText($randomToken);
        $stmtUpdate           = $link->prepare("UPDATE `admin_info` SET `Token` = ?, `enckey` = ? WHERE `username` = ?");
        $stmtUpdate->bind_param("sss", $randomToken, $encKey, $username);
        $stmtUpdate->execute();
        $_SESSION['token']    = ['user' => $username, 'token' => $loginToken];
        return ['token' => $loginToken];
    }
}