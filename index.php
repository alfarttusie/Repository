<?php

if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require_once  'php/tools.php';
require_once "php/lang.php";

class Index
{
    use Tools;

    function __construct()
    {
        $link = self::connectToDB();
        $lang = self::getLanguage($link);
        Lang::load($lang ?? 'en');
        self::HeaderView();
        if (self::SysTemCheck($response)) {
            session_start();
            $token = $_SESSION['session_token'] ?? null;
            if ($token && self::loginChecker($token)) return header('Location: home.php');
            self::View();
        } else {
            http_response_code(503);
            self::Msg($response ?? 'empty');
        }

        self::FooterView();
    }
    private static function HeaderView()
    {
        echo '
            <!DOCTYPE html>
            <tml lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>' . lang::get('login-title') . '</title>
                <link rel="stylesheet" href="css/main.css">
                <link rel="stylesheet" href="css/index.css">
                <link rel="stylesheet" href="css/animation.css">
                <link rel="stylesheet" href="css/elements.css">
            </head>
            <body style="direction: ' . (lang::get('direction')) . '">
            
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
            <button class="language-btn" id="lang-btn">' . lang::get('language-btn') . '</button>
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
            <script>
                class Translator {
                    constructor() {
                        this.data = ' . json_encode(lang::getAll(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '
                        }   
                        get(key) {
                            return this.data[key] || key;
                        }
                        test(){
                            console.log(`thidatadsdcvsdf`);
                        }
                    }
                    const lang = new Translator();
                    </script>
            </body>
            </html>
        ';
    }
}

new Index();
