<?php
if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require_once  'php/tools.php';
require_once "php/lang.php";

class Index
{
    use Tools;

    function __construct()
    {
        $SESSION = isset($_COOKIE['session_token'])
            ? json_decode($_COOKIE['session_token'], true)
            : null;

        if ($SESSION && self::loginChecker($SESSION))
            return exit(header('Location: home.php'));

        $lang = $_GET['lang'] ?? 'en';
        Lang::load($lang ?? 'en');
        self::HeaderView();
        $response = 'unknown error';
        if (self::SysTemCheck($response)) {
            self::$connection = self::connectToDB();
            $lang = self::getLanguage(self::$connection);
            lang::load($lang ?? 'en');
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
                <p class="error-title">' . lang::get('error-title') . '</p>
                    <div class="error-text">
                        ' . htmlspecialchars(Lang::get($error), ENT_QUOTES, 'UTF-8') . '
                    </div>
                    <a class="error-btn" href="index.php?lang=' . lang::get('lang-revers') . '">' . lang::get('language-btn') . '</a>
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
    function __destruct()
    {
        if (isset(self::$connection)) self::$connection->close();
    }
}

new Index();
