<?php

if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require_once  'php/tools.php';
require_once "php/lang.php";

$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'ar';


Lang::load($lang ?? 'en');

class Index
{
    use Tools;

    function __construct()
    {
        session_start();
        $token = $_SESSION['session_token'] ?? null;
        if ($token && self::loginChecker($token)) return header('Location: home.php');

        self::HeaderView();
        if (self::SysTemCheck($response))
            self::View();
        else {
            http_response_code(503);
            self::Msg($response ?? 'empty');
        }

        self::FooterView();
    }
    private static function HeaderView()
    {
        echo '
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>' . lang::get('login-title') . '</title>
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
            <input type="text" class="username" placeholder="' . lang::get('login-username') . '">
            <div class="password-field">
                <input type="password" class="password" placeholder="' . lang::get('login-password') . '">
                <button class="view-password">🙈</button>
            </div>
            <button class="login-btn">' . lang::get('login-submit') . '</button>
            <a class="language-btn login-btn" href=' . lang::get('language') . '>' . lang::get('language-btn') . '</a>
        </div>
        <script src="js/assistant.js"></script>
        <script src="js/index.js"></script>
        ';
    }
    private static function Msg($error)
    {
        print('
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

new Index();
