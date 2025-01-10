<?php
if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require 'php/tools.php';

class Index
{
    use Tools;

    function __construct()
    {
        session_start();
        if (!self::SysTemCheck($response)) {
            return self::Msg($response);
        }

        $token = $_SESSION['session_token'] ?? null;
        if ($token && self::loginChecker($token)) {
            header('Location: home.php');
            exit;
        }

        self::View();
    }

    private static function View()
    {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المستودع</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/animation.css">
    <link rel="stylesheet" href="css/elements.css">
</head>

<body>
    <div class="indicator">
        <p></p>
    </div>
    <div class="login-holder">
        <input type="text" class="username" placeholder="User name">
        <input type="password" class="password" placeholder="Password">
        <button class="view-password">🙈</button>
        <button class="login-btn">Login</button>
    </div>
    <script src="js/index.js"></script>
    <script src="js/assistant.js"></script>
</body>

</html>
<?php
    }

    private static function Msg($error)
    {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/error.css">
</head>

<body>
    <div class="error-holder">
        <div class="error-text">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    </div>
</body>

</html>
<?php
    }
}

new Index();