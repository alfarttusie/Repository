<?php
error_reporting(0);

require 'tools.php';
require 'response_handler.php';
require 'signin.php';
require 'queries.php';

class Requests
{
    use Tools;
    private static function logOut()
    {
        $bearerToken = self::Headers();
        $username = $bearerToken['user'] ?? null;
        $token = $bearerToken['token'] ?? null;

        $link = self::connectToDB();

        $stmt = $link->prepare("DELETE FROM `auth_tokens` WHERE `username` = ? AND `token` = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $token);
        $stmt->execute();

        return new Response(200, ['status' => 'logged out']);
    }

    private static function SetKey($data, $link)
    {

        $key = @$data['key'] ?? null;

        if (!$key) return new Response(400, ['debug' => 'empty key']);

        $link = self::connectToDB();
        $bearerToken = self::Headers();
        $user = $bearerToken['user'] ?? null;
        $stmt = $link->prepare("SELECT `enckey` FROM `admin_info` WHERE `username` = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $sqlKey = $stmt->get_result()->fetch_assoc()['enckey'];

        $enckey = self::encryptText($key, $sqlKey);
        $response['response'] = (self::verifyEncryptedKey($key, $link)) ? "update" : "save";
        new Response(200, $response, payload: ['key' => $enckey]);
    }
    private static function verifyEncryptedKey(&$key, $link)
    {
        $bearerToken = self::Headers();
        if ($bearerToken != 'empty') {
            $key  = @$bearerToken['key'] ?? null;
            $user = $bearerToken['user'] ?? null;
            if ($key && $user) {
                $sql_key = $link->query("SELECT `enckey` FROM `admin_info` WHERE `username` = '$user'")->fetch_assoc()['enckey'];
                $key = self::decryptText($key, $sql_key);
                return true;
            } else
                return false;
        } else {
            return false;
        }
    }
    public function __construct($data)
    {
        try {

            $headers = @getallheaders();
            $bearerToken = $headers['Bearer'] ?? null;
            $data = json_decode($data, true);
            $type = @$data['type'] ?? 'empty';

            $commands = ['init session', 'sign in', 'Key checker', 'Set Key', 'log out', 'queries'];

            if ($type == 'empty' || $type == '') return new Response(400, ['debug' => 'Type not Set']);
            if (!in_array($type, $commands)) return new Response(400, ['debug' => 'Type not match']);


            /** System check */
            if (!self::SysTemCheck($response)) return new Response(500, ['debug' => $response]);

            /** create new link */
            self::$connection = self::connectToDB();

            /** Before login */
            if ($type == 'init session')
                return self::loginChecker(self::$connection) ? new Response(200, ['status' => 'logedin']) :  new Response(200);

            if (!self::Postvalidation($response)) return new Response(400, ['debug' => $response]);

            if (!self::verifyJwt($bearerToken)) return new Response(400, ['debug' => 'invalid jwt token']);

            if ($type == 'sign in')
                return new Signin(Post: $data, link: self::$connection);


            /** after logged in*/
            if (!self::loginChecker(self::$connection))
                return new Response(400, ['debug' => 'invalid login']);

            switch ($type) {
                case 'Key checker':
                    self::verifyEncryptedKey($key, self::$connection) ? new Response(200, ['status' => 'successful']) : new Response(200, ['status' => 'invalid key']);
                    return;
                case 'Set Key':
                    self::SetKey($data, self::$connection);
                    return;
                case 'log out':
                    self::logOut($data);
                    return;
            }

            /** after Key Setup */

            if (!self::verifyEncryptedKey($key, self::$connection)) return new Response(200, ['response' => 'invalid key']);


            switch ($type) {
                case 'queries':
                    return new queries(post: $data, key: $key, link: self::$connection);
                    break;
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