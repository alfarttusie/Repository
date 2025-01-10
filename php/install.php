<?php

class install
{
    private static function Response($code, $data)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        print_r(json_encode($data));
        return;
    }
    private static function db_check($Serverip)
    {
        try {
            $socket = @fsockopen($Serverip, 3306, $errno, $errstr, 4);
            return $socket ? (fclose($socket) || true) : false;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function DataBase_Info($user, $password, $Serverip)
    {
        try {
            $dbConnection = @new mysqli($Serverip, $user, $password);
            $dbConnection->close();
            return !$dbConnection->connect_error;
        } catch (Exception $error) {
            return false;
        }
    }
    private static function isDatabaseAvailable($user, $password, $Serverip, $database)
    {
        try {
            $dbConnection = @new mysqli($Serverip, $user, $password);
            return $dbConnection->select_db($database);
        } catch (Exception $error) {
            return false;
        }
    }
    private static function CreatDatabase($user, $password, $database, $Serverip)
    {
        $link = new mysqli($Serverip, $user, $password);

        $database = $link->real_escape_string($database);
        $link->query("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");

        $link->select_db($database);

        $sql = "
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
                ('admin', '\$2y\$10\$dxdmygPf/jnCYuzXv7PRg.XO.5qfKhXOGsV.XETb046Sg/.ewO4re', '', '');
    
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

        $link->multi_query($sql);
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
        if (file_exists('db.php')) return self::Response(200, ['error' => 'repository already installed']);
        $Post = json_decode($Post, true);
        $db_user = $Post['db_username'] ?? null;
        $db_password = $Post['db_password'] ?? "";
        $db_name = $Post['db_name'] ?? null;
        $Serverip = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';


        if (!self::db_check($Serverip)) return self::Response(200, ['error' => 'mysql services off']);

        if (empty($db_user) || empty($db_name)) return self::Response(200, ['error' => 'missing info']);

        if (!self::DataBase_Info($db_user, $db_password, $Serverip)) return self::Response(200, ['error' => 'wrong info']);

        if (self::isDatabaseAvailable($db_user, $db_password, $Serverip, $db_name)) return self::Response(200, ['error' => 'database exists']);

        self::CreatDatabase($db_user, $db_password, $db_name, $Serverip);
        $file_content = preg_replace('/^\s+/', '', "
        <?php
        \rtrait database{
            \r\tprivate static  \$Serverip =  '" . $Serverip . "';

            \r\tprivate  static \$ServerUser = '" . $db_user . "';

            \r\tprivate  static \$ServerPassword = '" . $db_password . "';

            \r\tprivate  static \$database = '" . $db_name . "';

            \r\tprivate  static \$secret = '" . self::generateRandomString(random_int(250, 500)) . "';
        \r\t}
        
        ");
        if (!file_put_contents('db.php', $file_content)) {
            return self::Response(500, ['error' => 'Failed to create database file']);
        }
        return self::Response(200, ['success' => 'ok']);
    }
}
$PostData = file_get_contents('php://input');
($_SERVER["REQUEST_METHOD"] == "POST") ? new install($PostData) : exit("Only Post METHODs");