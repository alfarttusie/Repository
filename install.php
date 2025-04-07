<?php

/**
 * RepositoryInstaller class handles the installation of the repository.
 * It checks for the existence of a database file and displays an installation form in either English or Arabic.
 */
class RepositoryInstaller
{
    /**
     * Returns the HTML view in English.
     *
     * @return string
     */
    private function getEnglishView()
    {
        return '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>Install - Repository</title>
                    <link rel="stylesheet" href="css/main.css" />
                    <link rel="stylesheet" href="css/animation.css" />
                    <link rel="stylesheet" href="css/install.css" />
                </head>
                <body>
                    <form class="install-holder">
                        <div class="input-group">
                            <label for="db_username">MySQL User:</label>
                            <input type="text" id="db_username" class="db_username" placeholder="Enter MySQL Username" required />
                        </div>
                        <div class="input-group">
                            <label for="db_password">MySQL Password:</label>
                            <input type="password" id="db_password" class="db_password" placeholder="Enter Password (leave it empty if none)" />
                        </div>
                        <div class="input-group">
                            <label for="db_name">Database Name:</label>
                            <input type="text" id="db_name" class="db_name" placeholder="Enter Database Name" required />
                        </div>
                        <div class="input-group">
                            <label for="username">Administration :</label>
                            <input type="text" id="username" class="username" placeholder="Enter login Username" required />
                        </div>
                        <div class="input-group">
                            <label for="Password">Administration Password:</label>
                            <input type="text" id="Password" class="Password" placeholder="Enter Administration Password" required />
                        </div>
                        <button type="submit" class="install-button" value="install">Install</button>
                        <button type="button" class="lang-button" onclick="window.location.href=\'?lang=ar\'">عربي</button>
                    </form>
                    <script src="js/install.js"></script>
                    <script src="js/assistant.js"></script>
                </body>
                </html>';
    }

    /**
     * Returns the HTML view in Arabic.
     *
     * @return string
     */
    private function getArabicView()
    {
        return '<!DOCTYPE html>
                <html lang="ar" dir="rtl">
                <head>
                    <meta charset="UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>تثبيت - المستودع</title>
                    <link rel="stylesheet" href="css/main.css" />
                    <link rel="stylesheet" href="css/animation.css" />
                    <link rel="stylesheet" href="css/install.css" />
                </head>
                <body>
                    <form class="install-holder">
                        <div class="input-group">
                            <label for="db_username">مستخدم MySQL:</label>
                            <input type="text" id="db_username" class="db_username" placeholder="أدخل اسم مستخدم MySQL" required />
                        </div>
                        <div class="input-group">
                            <label for="db_password">كلمة مرور MySQL:</label>
                            <input type="password" id="db_password" class="db_password" placeholder="أدخل كلمة المرور (اتركها فارغة إذا لم تكن موجودة)" />
                        </div>
                        <div class="input-group">
                            <label for="db_name">اسم قاعدة البيانات:</label>
                            <input type="text" id="db_name" class="db_name" placeholder="أدخل اسم قاعدة البيانات" required />
                        </div>
                        <div class="input-group">
                            <label for="username">الإدارة:</label>
                            <input type="text" id="username" class="username" placeholder="أدخل اسم المستخدم للدخول" required />
                        </div>
                        <div class="input-group">
                            <label for="Password">كلمة مرور الإدارة:</label>
                            <input type="text" id="Password" class="Password" placeholder="أدخل كلمة مرور الإدارة" required />
                        </div>
                        <button type="submit" class="install-button" value="install">تثبيت</button>
                        <button type="button" class="lang-button" onclick="window.location.href=\'?lang=en\'">English</button>
                    </form>
                    <script src="js/install.js"></script>
                    <script src="js/assistant.js"></script>
                </body>
                </html>';
    }

    /**
     * Constructor checks for the existence of the database file and displays the appropriate language view.
     */
    public function __construct()
    {
        if (file_exists('php/db.php'))
            exit(header('Location: index.php'));


        $language = isset($_GET['lang']) ? $_GET['lang'] : 'en';

        if ($language === 'ar') {
            echo $this->getArabicView();
        } else {
            echo $this->getEnglishView();
        }
    }
}

new RepositoryInstaller();

echo "hello";
