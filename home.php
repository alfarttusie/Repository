<?php
if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require 'php/tools.php';

class Home
{
    use Tools;
    function __construct()
    {
        session_start();
        $Token = @$_SESSION['session_token'] ?? null;
        if ($Token && self::loginChecker($Token))
            self::View();
        else
            header('Location: index.php');
        exit;
    }
    private static function View()
    {
        print("
            <!DOCTYPE html>
                <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <link rel='stylesheet' href='css/main.css'>
                        <link rel='stylesheet' href='css/home.css'>
                        <link rel='stylesheet' href='css/animation.css'>
                        <link rel='stylesheet' href='css/elements.css'>
                        <link rel='stylesheet' href='css/key.css'>
                        <link rel='stylesheet' href='css/notification.css'>
                        <link rel='stylesheet' href='css/contextMenu.css'>
                        <link rel='stylesheet' href='css/insertdata.css'>
                        <link rel='stylesheet' href='css/showButton.css'>
                        <script src='js/assistant.js'></script>
                        <title>المستودع</title>
                    </head>
                <body>
                    <div class='indicator'><p></p></div>
                        <div class='header'>
                            <button class='header-btn' id='logout'>خروج</button>
                            <button class='header-btn'>test</button>
                            <button class='header-btn' id='key'>المفتاح</button>
                            <button class='header-btn'>test</button>
                            <button class='header-btn' id='addButton'>اظافة زر</button>
                            <button class='header-btn' id='home'>الرئيسية</button>
                        </div>
                    
                        <div class='left'>
                        </div>
                    
                        <div class='right'>
                        </div>
                        
                    
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
}
new Home();
