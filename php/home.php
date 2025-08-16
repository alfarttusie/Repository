<?php
error_reporting(0);

if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require 'php/tools.php';
require_once "php/lang.php";

class Home
{
    use Tools;
    function __construct()
    {
        $resonse = null;

        if (!self::SysTemCheck($resonse)) {
            header('Location: index.php');
            exit;
        }

        self::$connection = self::connectToDB();
        $SESSION = isset($_COOKIE['session_token'])
            ? json_decode($_COOKIE['session_token'], true)
            : null;

        if ($SESSION && isset($SESSION['user'], $SESSION['token']) && self::loginChecker(self::$connection, $SESSION)) {
            Lang::load(self::getLanguage(self::$connection) ?? 'en');
            self::View();
        } else {
            header('Location: index.php');
            exit;
        }
    }
    private static function View()
    {
        print("
            <!DOCTYPE html>
                <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1, viewport-fit=cover'>
                        <meta http-equiv='X-UA-Compatible' content='IE=edge' />
                        <link rel='stylesheet' href='css/main.css'>
                        <link rel='stylesheet' href='css/home.css'>
                        <link rel='stylesheet' href='css/animation.css'>
                        <link rel='stylesheet' href='css/elements.css'>
                        <link rel='stylesheet' href='css/key.css'>
                        <link rel='stylesheet' href='css/notification.css'>
                        <link rel='stylesheet' href='css/contextMenu.css'>
                        <link rel='stylesheet' href='css/insertdata.css'>
                        <link rel='stylesheet' href='css/showbutton.css'>
                        <link rel='stylesheet' href='css/addbutton.css'>
                        <link rel='stylesheet' href='css/ButtonSettings.css'>
                        <script src='js/assistant.js'></script>
                        <title>" . lang::get('home-title') . "</title>
                    </head>
                <body style='direction: " . (lang::get('direction')) . "'>
                    <div class='indicator'><p></p></div>
                        <div class='header'>
                            <button class='header-btn' id='logout'>" . lang::get('logout-btn') . "</button>
                            <button class='header-btn' id='key'>" . lang::get('key-btn') . "</button>
                            <button class='header-btn' id='settings'>" . lang::get('settings-btn') . "</button>
                            <button class='header-btn' id='lang-btn'>" . lang::get('language-btn') . "</button>
                            <button class='header-btn' id='addButton'>" . lang::get('add-btn') . "</button>
                            <button class='header-btn' id='home'>" . lang::get('home-btn') . "</button>
                        </div>
                    
                        <div class='left'>
                        </div>
                    
                        <div class='right'>
                        </div>
                        
                    <script>
                    class Translator {
                        constructor() {
                            this.data = " . json_encode(lang::getAll(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ";
                            }   
                            get(key) {
                                return this.data[key] || key;
                            }
                        }
                    const lang = new Translator();
                    </script>

                    <script src='js/addbutton.js'></script>
                    <script src='js/contextMenu.js'></script>
                    <script src='js/key.js'></script>
                    <script src='js/insertData.js'></script>
                    <script src='js/ButtonSettings.js'></script>
                    <script src='js/ShowButton.js'></script>
                    <script src='js/home.js'></script>
                </body>

                </html>
");
    }
    function __destruct()
    {
        if (self::$connection) {
            self::$connection->close();
        }
    }
}
new Home();
