<?php

error_reporting(0);

if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require_once  'php/tools.php';
require_once "php/lang.php";

class Index
{
    use Tools;
    function __construct()
    {

        $response = 'unknown error';
        if (self::SysTemCheck($response)) {
            self::$connection = self::connectToDB();
            $SESSION = isset($_COOKIE['session_token'])
                ? json_decode($_COOKIE['session_token'], true)
                : null;
            if ($SESSION && self::loginChecker(self::$connection, $SESSION)) {
                header('Location: home.php');
                exit;
            }

            self::Pagerender();
            self::FooterView();
        } else {
            http_response_code(503);
            self::Msg($response ?? 'empty');
        }
    }
    private static function Pagerender()
    {
        $lang = $_GET['lang'] ?? self::getLanguage(self::$connection) ?? 'en';
        Lang::load($lang);
        echo "
                <!DOCTYPE html>
                <html lang='en' dir='" . Lang::get('direction') . "'>

                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <meta name='theme-color' content='#1f1f1f'>
                        <title> " . Lang::get('login-title') . " </title>
                        <link rel='stylesheet' href='css/main.css'>
                        <link rel='stylesheet' href='css/index.css'>
                        <link rel='stylesheet' href='css/animation.css'>
                        <link rel='stylesheet' href='css/elements.css'>
                    </head>
                    <body>
                            <div class='indicator'>
                        <p></p>
                    </div>
                    <div class='login-holder'>
                        <input type='text' class='username' placeholder='" . Lang::get('login-username') . "'>
                        <div class='password-field'>
                            <input type='password' class='password' placeholder='" . Lang::get('login-password') . "'>
                            <button class='view-password'>🙈</button>
                        </div>
                        <button class='login-btn'>" . Lang::get('login-submit') . "</button>
                        <a class='language-btn' href='index.php?lang=" . Lang::get('lang-revers') . "'>" . Lang::get('language-btn') . "</a>
                    </div>
  
            ";
    }
    private static function Msg($error)
    {
        $lang = $_GET['lang'] ?? 'en';
        Lang::load($lang);
        print("
            <!DOCTYPE html>
                <html lang='en' dir='" . Lang::get('direction') . "'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <meta name='theme-color' content='#1f1f1f'>
                        <title> " . Lang::get('login-title') . " </title>
                        <link rel='stylesheet' href='css/main.css'>
                        <link rel='stylesheet' href='css/index.css'>
                        <link rel='stylesheet' href='css/animation.css'>
                        <link rel='stylesheet' href='css/elements.css'>
                    </head>
                    <body>
                <div class='error-holder'>
                <p class='error-title'>" . Lang::get('error-title') . "</p>
                    <div class='error-text'>
                        " . htmlspecialchars(Lang::get($error), ENT_QUOTES, 'UTF-8') . "
                    </div>
                    <a class='error-btn' href='index.php?lang=" . Lang::get('lang-revers') . "'>" . Lang::get('language-btn') . "</a>
                </div>
                </body>
                </html>
        ");
    }
    private static function FooterView()
    {
        $jwt = json_encode(self::createJwt([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'exp' => time() + 3600,
        ]), JSON_UNESCAPED_SLASHES);
        echo '
            <script>
                class Translator {
                    constructor() {
                        this.data = ' . json_encode(Lang::getAll(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';
                    }   
                    get(key) {
                        return this.data[key] || key;
                    }
                }
                const lang = new Translator();
                localStorage.bearer = ' . $jwt . ';
            </script>
            <script src="js/assistant.js"></script>
            <script src="js/index.js"></script>
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
