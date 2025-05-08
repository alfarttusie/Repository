<?php
require 'jwt.php';
require 'encryption.php';
require 'ArrayHelper.php';
// error_reporting(0);
trait Tools
{
    use Jwt;
    use encryption;
    use ArrayHelper;

    private static $connection;

    private static function generateRandomString($length = null): string
    {
        $length = (is_int($length) && $length > 0) ? $length : rand(10, 25);
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomStr = '';
        for ($i = 0; $i < $length; $i++) {
            $randomStr .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomStr;
    }
    private static function isMySqlServerReachable(): bool
    {
        try {
            $socket = fsockopen(self::$Serverip, 3306, $errno, $errstr, 4);
            return $socket ? (fclose($socket) || true) : false;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function DataBase_Info()
    {
        try {
            $dbConnection = new mysqli(self::$Serverip, self::$ServerUser, self::$ServerPassword);
            return !$dbConnection->connect_error;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function isDatabaseAvailable()
    {
        try {
            $dbConnection = new mysqli(self::$Serverip, self::$ServerUser, self::$ServerPassword);
            $status = false;
            if ($dbConnection->select_db(self::$database)) {
                $status =  true;
            } else {
                $dbConnection->close();
                $status =  false;
            }
            $dbConnection->close();
            return $status;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function connectToDB()
    {
        self::$connection = new mysqli(self::$Serverip, self::$ServerUser, self::$ServerPassword, self::$database);
        return self::$connection;
    }

    private static function BotChecker()
    {
        try {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            if (empty($userAgent))
                return false;

            $nonBrowserSubstrings = ['curl', 'wget', 'httpie', 'python', 'ruby', 'java'];

            foreach ($nonBrowserSubstrings as $substring) {
                if (stripos($userAgent, $substring) !== false) {
                    return false;
                }
            }
            $bots = ['googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot', 'sogou', 'exabot', 'facebot', 'ia_archiver', 'PostmanRuntime'];
            foreach ($bots as $bot) {
                if (stripos($userAgent, $bot) !== false) {
                    return false;
                }
            }
            return true;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function SysTemCheck(&$response)
    {
        try {
            if (!self::isMySqlServerReachable()) throw new Exception('offline-database');

            if (!self::DataBase_Info())  throw new Exception('database-cerdentials-error');

            if (!self::isDatabaseAvailable()) throw new Exception('database-error');

            return true;
        } catch (Exception $e) {
            $response = $e->getMessage();
            return false;
        }
    }
    private static function PostValidation(&$response)
    {
        try {
            $response = 'empty';
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
            $origin = $headers['origin'] ?? null;
            $contentType = $headers['content-type'] ?? null;
            $payload = self::Headers();

            if (!self::BotChecker())
                throw new Exception('Suspect as a bot');
            if (empty($payload))
                throw new Exception('JWT token needed');
            if (stripos($contentType, 'application/json') === false)
                throw new Exception('Data not JSON');

            return true;
        } catch (Exception $exception) {
            $response = $exception->getMessage();
            return false;
        }
    }

    private static function loginChecker($link, $Bearer = null)
    {
        try {
            if (!$Bearer)
                $Bearer = self::Headers();

            $username = $Bearer['user'] ?? '';
            $token    = $Bearer['token'] ?? '';

            if (empty($username) || empty($token))
                return false;

            $stmt = $link->prepare("SELECT `id`, `created_at` FROM `auth_tokens`  WHERE `username` = ? AND `token` = ? LIMIT 1");
            $stmt->bind_param("ss", $username, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $tokenRow = $result->fetch_assoc();

            if (!$tokenRow)
                return false;

            $createdTime = strtotime($tokenRow['created_at']);
            $now = time();
            $hoursPassed = ($now - $createdTime) / 3600;

            if ($hoursPassed > 24) {
                $stmtDelete = $link->prepare("DELETE FROM `auth_tokens` WHERE `id` = ?");
                $stmtDelete->bind_param("i", $tokenRow['id']);
                $stmtDelete->execute();
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    private static function Headers()
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $jwt = $headers['bearer'] ?? null;

        if (!$jwt)
            return ['error' => 'JWT token missing'];

        $data = self::verifyJwt($jwt);

        return ($data['valid'] ?? false) && is_array($data['payload'] ?? null)
            ? $data['payload']
            : ['error' => 'Invalid JWT token'];
    }
    private static function deleteExpiredTokens(mysqli $link): void
    {
        $stmt = $link->prepare("DELETE FROM `auth_tokens` WHERE `created_at` < NOW() - INTERVAL 1 DAY");
        $stmt->execute();
    }
    private static function getLanguage(mysqli $link): string
    {
        $lang = 'en';

        try {
            $stmt = $link->prepare("SELECT `lang` FROM `setting` WHERE `id` = 1 LIMIT 1");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                $langFromDb = trim($row['lang'] ?? '');
                if (!empty($langFromDb)) {
                    $lang = $langFromDb;
                }
            }
        } catch (Exception $e) {
            return $lang;
        }

        return $lang;
    }
}
