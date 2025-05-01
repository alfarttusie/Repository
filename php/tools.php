<?php
require 'jwt.php';
require 'encryption.php';
require 'settings.php';
require 'ArrayHelper.php';
trait Tools
{
    use Jwt;
    use encryption;
    use Settings;
    use ArrayHelper;

    private static $Bearer;
    private static $connection;

    private static function generateRandomString($length = null): string
    {
        $length = ($length == null) ? rand(10, 25) : $length;
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $randomStr = '';
        for ($i = 0; $i < $length; $i++)
            $randomStr .= $characters[random_int(0, strlen($characters) - 1)];

        return $randomStr;
    }
    private static function isMySqlServerReachable(): bool
    {
        try {
            $socket = @fsockopen(self::$Serverip, 3306, $errno, $errstr, 4);
            return $socket ? (fclose($socket) || true) : false;
        } catch (Exception $error) {
            return false;
        }
    }

    private static function DataBase_Info()
    {
        try {
            $dbConnection = @new mysqli(self::$Serverip, self::$ServerUser, self::$ServerPassword);
            return !$dbConnection->connect_error;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function isDatabaseAvailable()
    {
        try {
            $dbConnection = @new mysqli(self::$Serverip, self::$ServerUser, self::$ServerPassword);
            return $dbConnection->select_db(self::$database);
        } catch (Exception $error) {
            return false;
        }
    }
    private static function connectToDB()
    {
        try {
            $dbConnection = @new mysqli(self::$Serverip, self::$ServerUser, self::$ServerPassword, self::$database);
            return $dbConnection;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function BotChecker()
    {
        try {
            $userAgent = @$_SERVER['HTTP_USER_AGENT'];
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
            if (!self::isMySqlServerReachable()) throw new Exception('MySQL server is offline');

            if (!self::DataBase_Info())  throw new Exception('Invalid MySQL information');

            if (!self::isDatabaseAvailable()) throw new Exception('Database is unavailable');

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
            $headers = getallheaders();
            $origin = $headers['origin'] ?? null;
            $contentType = $headers['Content-type'] ?? null;
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

            $stmt = $link->prepare("
            SELECT `id`, `created_at` 
            FROM `auth_tokens` 
            WHERE `username` = ? AND `token` = ? 
            LIMIT 1
        ");
            $stmt->bind_param("ss", $username, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $tokenRow = $result->fetch_assoc();

            if (!$tokenRow) {
                return false;
            }

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
        $headers = @getallheaders();
        $jwt = $headers['Bearer'] ?? null;

        if (!$jwt) return [];

        $data = self::verifyJwt($jwt);

        return is_array($data['payload'] ?? null) ? $data['payload'] : [];
    }
    private static function deleteExpiredTokens(mysqli $link): void
    {
        $stmt = $link->prepare("DELETE FROM `auth_tokens` WHERE `created_at` < (NOW() - INTERVAL 1 DAY)");
        $stmt->execute();
    }
}