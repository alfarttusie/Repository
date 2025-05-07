<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>لوحة الإعدادات</title>
    <style>
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        color: #333;
        display: flex;
    }

    .sidebar {
        width: 220px;
        min-height: 100vh;
        background-color: #2f3640;
        color: white;
        padding: 30px 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
    }

    .sidebar h2 {
        font-size: 20px;
        margin-bottom: 30px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .sidebar li {
        margin-bottom: 15px;
    }

    .sidebar a {
        text-decoration: none;
        color: #dcdde1;
        font-size: 16px;
        display: block;
        padding: 8px 10px;
        border-radius: 5px;
        transition: background 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #353b48;
    }

    .main {
        flex: 1;
        padding: 40px;
    }

    .dashboard {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }

    .card {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .card h3 {
        margin-bottom: 20px;
        font-size: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    .save-btn {
        margin-top: 20px;
        padding: 12px 20px;
        background-color: #00b894;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    .save-btn:hover {
        background-color: #019875;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>الإعدادات</h2>
        <ul>
            <li><a href="settings.php?page=password">🔐 كلمة المرور</a></li>
            <li><a href="settings.php?page=backup">💾 النسخ الاحتياطي</a></li>
            <li><a href="home.php">العودة</a></li>
            <li><a href="settings.php?page=login">🔑 إعدادات الدخول</a></li>
            <li><a href="settings.php?page=security">🛡️ الأمان</a></li>
            <li><a href="settings.php?language">🌐 اللغة</a></li>
        </ul>
    </div>

    <div class="main">
        <div class="dashboard">
            <?php
            define('IN_DASHBOARD', true);
            ob_start();
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
            ob_clean();
            // switch ($page) {
            //     case 'password':
            //         Settings::password();
            //         break;
            //     case 'backup':
            //         settings::backup();
            //         break;
            //     case 'login':
            //         Settings::login();
            //         break;
            //     case 'security':
            //         Settings::security();
            //         break;
            //     case 'language':
            //         Settings::language();
            //         break;
            //     default:
            //         Settings::home();
            //         break;
            // }
            ?>
        </div>
    </div>
</body>

</html>