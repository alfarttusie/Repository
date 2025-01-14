<?php
// error_reporting(0);
class install
{
    private static $dbConnection = null;
    private static function sendResponse($statusCode, $data)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        return;
    }
    private static function checkDatabaseConnection($Serverip)
    {
        try {
            $socket = @fsockopen($Serverip, 3306, $errno, $errstr, 4);
            return $socket ? (fclose($socket) || true) : false;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function validateDatabaseCredentials($user, $password, $Serverip)
    {
        try {
            $dbConnection = @new mysqli($Serverip, $user, $password);
            self::$dbConnection = $dbConnection;
            return !$dbConnection->connect_error;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function isDatabaseAccessible($user, $password, $Serverip, $database)
    {
        try {
            $dbConnection = @new mysqli($Serverip, $user, $password);
            return $dbConnection->select_db($database);
        } catch (Exception $error) {
            return false;
        }
    }
    private static function createDatabase($user, $password, $database, $Serverip, $loginUSer, $loginPassword)
    {
        $link = new mysqli($Serverip, $user, $password);

        $database = $link->real_escape_string($database);
        $link->query("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");

        $link->select_db($database);
        $loginPassword = password_hash($loginPassword, PASSWORD_DEFAULT);
        $createTables = "
                SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
                SET time_zone = '+00:00';
                
                CREATE TABLE IF NOT EXISTS `admin_info` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `username` text NOT NULL,
                    `password` text NOT NULL,
                    `Token` text NOT NULL,
                    `enckey` varchar(20) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    
                INSERT IGNORE INTO `admin_info` (`username`, `password`, `Token`, `enckey`) VALUES
                ('" . $loginUSer . "', '" . $loginPassword . "', '', '');
    
                CREATE TABLE IF NOT EXISTS `buttons` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `button` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
                    `main` text NOT NULL,
                    `password` text NOT NULL,
                    `unique_id` text NOT NULL,
                    `columns` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    
                CREATE TABLE IF NOT EXISTS `setting` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `times` int NOT NULL,
                    `time` int NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    
                INSERT IGNORE INTO `setting` (`times`, `time`) VALUES (6, 1);
        
                CREATE TABLE IF NOT EXISTS `visitors` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `times` int NOT NULL,
                    `ip` text NOT NULL,
                    `time` int NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";

        $link->multi_query($createTables);
        $link->close();
    }
    private static function generateRandomString($length = null): string
    {
        $length = ($length == null) ? rand(10, 25) : $length;
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $randomStr = '';
        for ($i = 0; $i < $length; $i++)
            $randomStr .= $characters[random_int(0, strlen($characters) - 1)];

        return $randomStr;
    }
    function __construct($Post)
    {
        if (file_exists('db.php')) return self::sendResponse(400, ['debug' => 'repository already installed']);
        $Post = json_decode($Post, true);
        $db_user = $Post['db_username'] ?? null;
        $db_password = $Post['db_password'] ?? "";
        $db_name = $Post['db_name'] ?? null;
        $Serverip = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        $loginUSer = $Post['username'] ?? null;
        $loginPassword = $Post['Password'] ?? null;


        if (empty($db_user) || empty($db_name))         return self::sendResponse(400, ['debug' => 'missing info']);
        if (empty($loginUSer) || empty($loginPassword)) return self::sendResponse(400, ['debug' => 'missing info']);

        if (!self::checkDatabaseConnection($Serverip)) return self::sendResponse(200, ['response' => 'mysql services off']);


        if (!self::validateDatabaseCredentials($db_user, $db_password, $Serverip)) return self::sendResponse(200, ['response' => 'wrong info']);

        $link = self::$dbConnection;
        $loginUSer = mysqli_real_escape_string($link, $loginUSer);
        $loginPassword = mysqli_real_escape_string($link, $loginPassword);
        $link->close();

        if (self::isDatabaseAccessible($db_user, $db_password, $Serverip, $db_name)) return self::sendResponse(200, ['response' => 'database exists']);

        self::createDatabase($db_user, $db_password, $db_name, $Serverip, $loginUSer, $loginPassword);
        $file_content = preg_replace('/^\s+/', '', "
        <?php
        \rtrait database{
            \r\tprivate static  \$Serverip =  '" . $Serverip . "';

            \r\tprivate  static \$ServerUser = '" . $db_user . "';

            \r\tprivate  static \$ServerPassword = '" . $db_password . "';

            \r\tprivate  static \$database = '" . $db_name . "';

            \r\tprivate  static \$secret = '" . self::generateRandomString(random_int(450, 900)) . "';
        \r\t}
        
        ");
        if (!file_put_contents('db.php', $file_content)) {
            return self::sendResponse(500, ['debug' => 'Failed to create database file']);
        }
        return self::sendResponse(200, ['success' => 'ok']);
    }
}
$PostData = file_get_contents('php://input');
($_SERVER["REQUEST_METHOD"] == "POST") ? new install($PostData) : exit("Only Post METHODs");
