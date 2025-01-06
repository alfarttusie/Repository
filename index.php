<?php

require 'php/tools.php';
class index
{
    use Tools;
    function __construct()
    {
        if (self::SysTemCheck($response)) {
            session_start();
            $Token = @$_SESSION['token'] ?? null;
            if ($Token && self::loginChecker($Token)) {
                exit(header('Location: home.php'));
            } else {
                index::View();
            }
        } else
            print($response);
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
}
new index();
