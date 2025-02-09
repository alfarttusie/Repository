<?php

if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require 'php/tools.php';

/**
 * Class Index
 * 
 * The main view controller for the application.
 * This class handles session initialization, system checks, 
 * and rendering the login or error pages.
 */
class Index
{
    use Tools;

    /**
     * Index constructor.
     * 
     * Initializes the session, regenerates the session ID,
     * checks system requirements, and redirects authenticated users.
     */
    function __construct()
    {
        session_start();
        session_regenerate_id(true);

        if (!self::SysTemCheck($response)) return self::Msg($response);


        $token = $_SESSION['session_token'] ?? null;
        if ($token && self::loginChecker($token)) return header('Location: home.php');


        self::View();
    }

    /**
     * Renders the login view.
     * 
     * Displays the login form with necessary styles and scripts.
     * This method outputs an HTML page directly.
     * 
     * @return void
     */
    private static function View()
    {
        print('
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
        ');
    }

    /**
     * Displays an error message.
     * 
     * Generates an HTML page with an error message to notify users of system issues.
     * 
     * @param string $error The error message to display.
     * @return void
     */
    private static function Msg($error)
    {
        print('
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
                        ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '
                    </div>
                </div>
            </body>

            </html>
        ');
    }
}

new Index();
