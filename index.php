<?php
if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require 'php/tools.php';
class index
{
    use Tools;
    function __construct()
    {
        if (!self::SysTemCheck($response)) return self::Msg($response);
        session_start();
        $Token = @$_SESSION['session_token'] ?? null;
        if ($Token && self::loginChecker($Token)) return header('Location: home.php');
        index::View();
    }
    private static function View()
    {
        print("
            <!DOCTYPE html>
            <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>المستودع</title>
                    <link rel='stylesheet' href='css/main.css'>
                    <link rel='stylesheet' href='css/index.css'>
                    <link rel='stylesheet' href='css/animation.css'>
                    <link rel='stylesheet' href='css/elements.css'>
                </head>
                <body>
                    <div class='indicator'><p></p></div>
                    <div class='login-holder'>
                        <input type='text' class='username' placeholder='User name' value='admin'>
                        <input type='password' class='password' placeholder='Password' value='iraq'>
                        <button class='view-password'>🙈</button>
                        <button class='login-btn'>login</button>
                    </div>
                    <script src='js/index.js'></script>
                    <script src='js/assistant.js'></script>
                </body>
            </html>
        ");
    }
    private static function Msg($error)
    {
        print("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8' />
            <meta name='viewport' content='width=device-width, initial-scale=1.0' />
            <title>system error</title>
            <link rel='stylesheet' href='css/main.css'>
            <link rel='stylesheet' href='css/error.css'>

        </head>
        <body>
        <div class='error-holder'>
            <div class='error-text'>
            $error
            </div>
        </div>
        </body>
        </html>
        ");
    }
}
new index();
