<?php

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
mb_internal_encoding("UTF-8");

class Install
{
    private static ?mysqli $dbConnection = null;

    /**
     * Sends a JSON response and exits execution.
     */
    private static function resJson(int $statusCode, array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Checks if the MySQL server is accessible.
     */
    private static function dbCheck(string $serverIp): bool
    {
        return (bool) @fsockopen($serverIp, 3306, $errno, $errstr, 2);
    }

    /**
     * Attempts to establish a database connection.
     */
    private static function dbAuth(string $user, string $password, string $serverIp): bool
    {
        try {
            self::$dbConnection = new mysqli($serverIp, $user, $password);
            return !self::$dbConnection->connect_error;
        } catch (mysqli_sql_exception) {
            return false;
        }
    }

    /**
     * Checks if the specified database exists.
     */
    private static function dbExists(string $database): bool
    {
        $database = self::$dbConnection->real_escape_string($database);
        $query = "SHOW DATABASES LIKE '$database'";
        $result = self::$dbConnection->query($query);
        return $result && $result->num_rows > 0;
    }

    /**
     * Generates a random string with a specified length.
     */
    private static function generateRandomString(): string
    {
        $length =  random_int(600, 900);
        return bin2hex(random_bytes(intdiv($length, 2)));
    }

    /**
     * Saves database connection details to `db.php` as a trait.
     */
    private static function saveDbConfig(string $dbUser, string $dbPassword, string $dbName, string $serverIp): void
    {
        $secretKey = self::generateRandomString();

        $config = "<?php\n";
        $config .= "trait DatabaseConfig {\n";
        $config .= "    private static string \$Serverip = '" . addslashes($serverIp) . "';\n";
        $config .= "    private static string \$ServerUser = '" . addslashes($dbUser) . "';\n";
        $config .= "    private static string \$ServerPassword = '" . addslashes($dbPassword) . "';\n";
        $config .= "    private static string \$database = '" . addslashes($dbName) . "';\n";
        $config .= "    private static string \$secret = '" . addslashes($secretKey) . "';\n";
        $config .= "}\n";

        if (!file_put_contents('db.php', $config)) {
            self::resJson(500, ['error' => 'Failed to create db.php']);
        }
    }

    /**
     * Install constructor
     * Handles the installation process by receiving POST data.
     */
    public function __construct(string $postData)
    {
        if (file_exists('db.php')) self::resJson(400, ['error' => 'installed']);

        if (!is_writable(__DIR__)) return self::resJson(200, ['error' => 'permission']);

        $post = json_decode($postData, true);

        if (!is_array($post)) self::resJson(400, ['error' => 'JSON']);

        $type = $post['type'] ?? 'install';
        $dbUser = $post['db_username'] ?? null;
        $dbPassword = $post['db_password'] ?? '';
        $dbName = $post['db_name'] ?? null;
        $serverIp = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        $loginUser = $post['username'] ?? null;
        $loginPassword = $post['Password'] ?? null;

        if (!$dbUser || !$dbName) self::resJson(400, ['error' => 'Missing required fields']);
        if (!self::dbCheck($serverIp)) self::resJson(503, ['error' => 'db_error']);
        if (!self::dbAuth($dbUser, $dbPassword, $serverIp)) self::resJson(401, ['error' => 'credentials']);

        if ($type === 'install') {
            if (!$loginUser || !$loginPassword) self::resJson(400, ['error' => 'Missing admin credentials for installation']);
            if (self::dbExists($dbName)) self::resJson(200, ['error' => 'exists']);

            self::dbCreate($dbName, $loginUser, $loginPassword);
        } elseif ($type === 'backup') {
            if (!self::dbExists($dbName)) self::resJson(404, ['error' => 'Database not found']);
        } else {
            self::resJson(400, ['error' => 'Invalid type specified']);
        }

        self::saveDbConfig($dbUser, $dbPassword, $dbName, $serverIp);
        self::resJson(201, ['success' => 'Configuration saved in db.php']);
    }

    /**
     * Creates the database and essential tables.
     */
    private static function dbCreate(string $database, string $adminUser, string $adminPassword): void
    {
        $conn = self::$dbConnection;
        $database = $conn->real_escape_string($database);
        $adminUser = $conn->real_escape_string($adminUser);
        $adminPassword = $conn->real_escape_string($adminPassword);
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $adminUser)) {
            self::resJson(400, ['error' => 'Invalid username format']);
        }
        $conn->query("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db($database);
        $adminPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

        $queries = "
            CREATE TABLE IF NOT EXISTS `admin_info` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(255) NOT NULL UNIQUE,
                `password` TEXT NOT NULL,
                `token` TEXT NOT NULL,
                `enckey` VARCHAR(20) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
            INSERT INTO `admin_info` (`username`, `password`, `token`, `enckey`) 
            VALUES ('$adminUser', '$adminPassword', '', '') 
            ON DUPLICATE KEY UPDATE username=username;
            
            CREATE TABLE IF NOT EXISTS `buttons` (
                `id` int NOT NULL AUTO_INCREMENT,
                `button` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
                `main` text NOT NULL,
                `password` text NOT NULL,
                `unique_id` text NOT NULL,
                `columns` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
            
            CREATE TABLE IF NOT EXISTS `setting` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `times` INT NOT NULL DEFAULT 6,
                `time` INT NOT NULL DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            
            INSERT IGNORE INTO `setting` (`times`, `time`) VALUES (6, 1);
            
            CREATE TABLE IF NOT EXISTS `visitors` (
                `id` int NOT NULL AUTO_INCREMENT,
                `times` int NOT NULL,
                `ip` text NOT NULL,
                `time` int NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            
            CREATE TABLE IF NOT EXISTS `auth_tokens` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `ip_address` VARCHAR(45),
                `user_agent` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

        if (!$conn->multi_query($queries)) {
            self::resJson(500, ['error' => 'Database creation failed: ' . $conn->error]);
        }
    }
}

$inputData = file_get_contents('php://input');
($_SERVER["REQUEST_METHOD"] === "POST") ? new Install($inputData) : exit("Only POST method is allowed.");