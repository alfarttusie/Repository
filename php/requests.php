<?php
error_reporting(0);

require 'tools.php';
require 'response_handler.php';
require 'signin.php';
require 'queries.php';
require 'settings.php';
require_once "lang.php";


class Requests
{
    use Tools;
    private static function logOut()
    {
        $bearerToken = self::Headers();
        $username = $bearerToken['user'] ?? null;
        $token = $bearerToken['token'] ?? null;

        $link = self::$connection;

        $stmt = $link->prepare("DELETE FROM `auth_tokens` WHERE `username` = ? AND `token` = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $token);
        $stmt->execute();

        return new Response(200, ['status' => 'logged out']);
    }
    private static function SetKey($data, $link)
    {
        try {
            $key = $data['key'] ?? null;
            if (!$key) return new Response(400, ['debug' => 'empty key']);

            $bearerToken = self::Headers();
            $user = $bearerToken['user'] ?? null;
            $token = $bearerToken['token'] ?? null;

            if (!$user) return new Response(400, ['debug' => 'User not authenticated']);

            $sql_key = self::generateRandomString(rand(10, 18));

            $stmt = $link->prepare("UPDATE `auth_tokens` SET `enckey` = ? WHERE `username` = ? AND `token` = ? LIMIT 1");
            $stmt->bind_param("sss", $sql_key, $user, $token);
            $stmt->execute();

            $enckey = self::encryptText($key, $sql_key);

            $response['response'] = (self::verifyEncryptedKey($key, $link)) ? "update" : "save";

            return new Response(200, $response, payload: ['key' => $enckey]);
        } catch (Exception $e) {
            return new Response(500, ['debug' => $e->getMessage()]);
        }
    }

    private static function verifyEncryptedKey(&$key, $link)
    {
        $bearerToken = self::Headers();
        $keyEncrypted = $bearerToken['key'] ?? null;
        $user = $bearerToken['user'] ?? null;
        $token = $bearerToken['token'] ?? null;

        if ($keyEncrypted && $user) {
            $stmt = $link->prepare("SELECT `enckey` FROM `auth_tokens` WHERE `username` = ?  AND `token` = ? LIMIT 1 ");
            $stmt->bind_param("ss", $user, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if (!$row) return false;

            $sql_key = $row['enckey'];
            $key = self::decryptText($keyEncrypted, $sql_key);
            return true;
        }
        return false;
    }
    private static function ChangeLang($lang, $link)
    {
        try {
            $stmt = $link->prepare("UPDATE `setting` SET `lang` = ? WHERE `id` = 1");
            $stmt->bind_param("s", $lang);
            $stmt->execute();
            return new Response(200);
        } catch (Exception $e) {
            return new Response(500, ['debug' => $e->getMessage()]);
        }
    }

    public function __construct($data)
    {
        try {
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            $bearerToken = @$headers['bearer'] ?? null;
            $data = json_decode($data, true);
            $type = @$data['type'] ?? 'empty';


            $response = 'unknown error';

            $commands = ['init session', 'sign in', 'Key checker', 'Set Key', 'log out', 'queries', 'lang', 'settings'];

            if ($type == 'empty' || $type == '') return new Response(400, ['debug' => 'Type not Set']);
            if (!in_array($type, $commands)) return new Response(400, ['debug' => 'Type not found']);


            /** System check */
            if (!self::SysTemCheck($response)) return new Response(500, ["message" => $response]);

            /** create new link */
            self::$connection = self::connectToDB();

            /** Before login */
            if ($type == 'init session')
                return self::loginChecker(self::$connection) ? new Response(200, ['status' => 'logedin']) :  new Response(200);


            if (!self::Postvalidation($response)) return new Response(400, ['debug' => $response]);

            if (empty($bearerToken)) return new Response(400, ['debug' => 'empty bearer Token']);

            $jwt_validation = self::verifyJwt($bearerToken) ?? null;
            if (!$jwt_validation['valid']) return new Response(400, ['debug' => $jwt_validation['error']]);


            if ($type == 'sign in')
                return new Signin(Post: $data, link: self::$connection);

            if ($type == 'lang')
                return self::ChangeLang($data['new'], self::$connection);

            /** after logged in*/
            if (!self::loginChecker(self::$connection))
                return new Response(400, ['debug' => 'invalid login']);

            switch ($type) {
                case 'Key checker':
                    return self::verifyEncryptedKey($key, self::$connection)
                        ? new Response(200, ['status' => 'successful'])
                        : new Response(200, ['status' => 'invalid key']);
                case 'Set Key':
                    return self::SetKey($data, self::$connection);
                case 'log out':
                    return self::logOut();
            }

            /** after Key Setup */

            if (!self::verifyEncryptedKey($key, self::$connection)) return new Response(200, ['response' => 'invalid key']);


            switch ($type) {
                case 'queries':
                    return new queries(post: $data, key: $key, link: self::$connection);
                    break;
                case 'settings':
                    return new settings(post: $data, key: $key, link: self::$connection);
                default:
                    return new Response(400, ['debug' => 'Type not match']);
                    break;
            }
            return new Response(400, ['debug' => 'Type not match']);
        } catch (Exception $exception) {
            new Response(500, ['debug' => $exception->getMessage()]);
        }
    }
    function __destruct()
    {
        if (self::$connection)
            self::$connection->close();
    }
}

$PostData = file_get_contents('php://input');
($_SERVER["REQUEST_METHOD"] == "POST") ? new Requests($PostData) : exit("Only Post METHODs");
