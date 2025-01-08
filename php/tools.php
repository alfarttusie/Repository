<?php
require 'jwt.php';
require 'encryption.php';
trait Tools
{
    use Jwt;
    use encryption;

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
    private static function isMySqlServerReachable()
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
    private static function loginChecker($Bearer = null)
    {
        try {
            if (!$Bearer)
                $Bearer = self::Headers();

            $user   = @$Bearer['user']  ?? 'empty';
            $token  = @$Bearer['token'] ?? 'empty';
            if (empty($user) || empty($token)) return false;

            $link   = self::connectToDB();

            if ($link) {
                $stmt = $link->prepare("SELECT `Token`, `enckey` FROM `admin_info` WHERE `username` = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $result = $stmt->get_result();
                $dbToken = $result->fetch_assoc();
                if ($dbToken) {
                    $encKey = hash('sha256', $dbToken['enckey'], true);
                    $Token = self::decryptText($token, $encKey);
                    $link->close();
                    return !empty($Token) && $Token === $dbToken['Token'] ? true : false;
                }
            }
            return false;
        } catch (Exception $Eexception) {
            return false;
        }
    }
    private static function Headers()
    {
        $headers = @getallheaders();
        $Bearer = $headers['Bearer'] ?? null;
        if ($Bearer) return self::verifyJwt($Bearer) ? $Bearer : [];
        else return [];
    }
    private static function Additem($array, $item)
    {
        array_push($array, $item);
        $array = array_values($array);
        return $array;
    }
    private static function replace_item(array $array, string $item, string $new)
    {
        $itemkey = array_search($item, $array);
        unset($array[$itemkey]);
        array_push($array, $new);
        return array_values($array);
    }
}
