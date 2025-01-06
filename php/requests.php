<?php
// error_reporting(0);


require 'tools.php';
require 'response_handler.php';
require 'Signin.php';
require 'queries.php';
class Requests
{
    use Tools;
    private static function logOut()
    {
        $bearerToken = self::Headers();
        $user = $bearerToken['user'];
        $link = self::connectToDB();
        $link->query("UPDATE `admin_info` SET `Token` = '', `enckey` = '' WHERE `username` = '$user'");
        new Response(200, ['status' => 'log out']);
    }
    private static function SetKey($data)
    {

        $key = @$data['key'];
        if (!empty($key)) {
            $link = self::connectToDB();
            $bearerToken = self::Headers();
            $user = $bearerToken['user'] ?? null;
            $stmt = $link->prepare("SELECT `enckey` FROM `admin_info` WHERE `username` = ?");
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $sqlKey = $stmt->get_result()->fetch_assoc()['enckey'];

            $enckey = self::encryptText($key, $sqlKey);
            $response['response'] = (self::Keychecker($key)) ? "update" : "save";
            new Response(200, $response, payload: ['key' => $enckey]);
        } else {
            new Response(400, ['debug' => 'empty key']);
        }
    }
    private static function Keychecker(&$key)
    {
        $bearerToken = self::Headers();
        if ($bearerToken != 'empty') {
            $key  = @$bearerToken['key'] ?? null;
            $user = $bearerToken['user'] ?? null;
            if ($key && $user) {
                $link = self::connectToDB();
                $sql_key = $link->query("SELECT `enckey` FROM `admin_info` WHERE `username` = '$user'")->fetch_assoc()['enckey'];
                $key = self::decryptText($key, $sql_key);
                $link->close();
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

            if ($type == 'empty' || $type == '') return new Response(200, ['debug' => 'Type not Set']);
            if (!in_array($type, $commands)) return new Response(400, ['debug' => 'Type not match']);

            /** System check */
            if (!self::SysTemCheck($response)) return new Response(500, ['debug' => $response]);

            /** Before login */
            if ($type == 'init session')
                return self::loginChecker() ? new Response(200, ['status' => 'logedin']) :  new Response(200);



            if (!self::Postvalidation($response)) return new Response(400, ['debug' => $response]);
            if (!self::verifyJwt($bearerToken)) return new Response(400, ['debug' => 'invalid jwt token']);

            if ($type == 'sign in') {
                self::$connection = self::connectToDB();
                return new Signin(Post: $data, link: self::$connection);
            }

            /** after logged in*/
            if (self::loginChecker())
                self::$connection = self::connectToDB();
            else
                return new Response(400, ['debug' => 'invalid login']);

            switch ($type) {
                case 'Key checker':
                    self::Keychecker($key) ? new Response(200, ['status' => 'successful']) : new Response(200, ['status' => 'invalid key']);
                    return;
                case 'Set Key':
                    self::SetKey($data);
                    return;
                case 'log out':
                    self::logOut($data);
                    return;
            }

            /** after Key Setup */

            if (!self::Keychecker($key)) return new Response(200, ['response' => 'invalid key']);


            switch ($type) {
                case 'queries':
                    return new queries(post: $data, key: $key, link: self::$connection);
                    break;
                default:
                    return new Response(400, ['debug' => 'Type not match']);
                    break;
            }
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