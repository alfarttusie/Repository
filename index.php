<<<<<<< HEAD
<?php

if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require 'php/tools.php';

class Index
{
    use Tools;

    function __construct()
    {
        session_start();
        session_regenerate_id(true);
        $token = $_SESSION['session_token'] ?? null;
        if ($token && self::loginChecker($token)) return header('Location: home.php');

        self::HeaderView();
        if (self::SysTemCheck($response))
            self::View();
        else
            self::Msg($response ?? 'empty');

        return self::FooterView();
    }
    private static function HeaderView()
    {
        echo '
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
        ';
    }
    private static function View()
    {
        echo '
        <div class="indicator">
            <p></p>
        </div>
        <div class="login-holder">
            <input type="text" class="username" placeholder="User name">
            <div class="password-field">
                <input type="password" class="password" placeholder="Password">
                <button class="view-password">🙈</button>
            </div>
            <button class="login-btn">Login</button>
        </div>
        <script src="js/assistant.js"></script>
        <script src="js/index.js"></script>
        ';
    }
    private static function Msg($error)
    {
        print('
            <body>
                <div class="error-holder">
                    <div class="error-text">
                        ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '
                    </div>
                </div>
        ');
    }
    private static function FooterView()
    {
        echo '
            </body>
            </html>
        ';
    }
}

=======
<?php

if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require 'php/tools.php';

class Index
{
    use Tools;

    function __construct()
    {
        session_start();
        session_regenerate_id(true);
        $token = $_SESSION['session_token'] ?? null;
        if ($token && self::loginChecker($token)) return header('Location: home.php');

        self::HeaderView();
        if (self::SysTemCheck($response))
            self::View();
        else
            self::Msg($response ?? 'empty');

        return self::FooterView();
    }
    private static function HeaderView()
    {
        echo '
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
        ';
    }
    private static function View()
    {
        echo '
        <div class="indicator">
            <p></p>
        </div>
        <div class="login-holder">
            <input type="text" class="username" placeholder="User name">
            <div class="password-field">
                <input type="password" class="password" placeholder="Password">
                <button class="view-password">🙈</button>
            </div>
            <button class="login-btn">Login</button>
        </div>
        <script src="js/assistant.js"></script>
        <script src="js/index.js"></script>
        ';
    }
    private static function Msg($error)
    {
        print('
            <body>
                <div class="error-holder">
                    <div class="error-text">
                        ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '
                    </div>
                </div>
        ');
    }
    private static function FooterView()
    {
        echo '
            </body>
            </html>
        ';
    }
}

>>>>>>> 6ab2c397ca3e2e87e98273bb53cabe79fcc7f241
new Index();