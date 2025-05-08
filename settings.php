<?php
if (!file_exists('php/db.php')) exit(header('Location: install.php'));

require_once 'php/tools.php';
require_once 'php/lang.php';

class SettingsPage
{
    use Tools;

    public function __construct()
    {

        if (!self::SysTemCheck($response)) return exit(header('Location: index.php'));

        self::$connection = self::connectToDB();

        $lang = self::getLanguage(self::$connection);
        lang::load($lang ?? 'en');

        $SESSION = isset($_COOKIE['session_token'])
            ? json_decode($_COOKIE['session_token'], true)
            : null;

        if (!$SESSION || !self::loginChecker(self::$connection, $SESSION))
            exit(header('Location: index.php'));

        $this->render();
    }

    private function render()
    {
        $page = $_GET['page'] ?? 'home';

        echo '<!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>' . lang::get("settings-btn") . '</title>
            <link rel="stylesheet" href="css/settings.css">
            <link rel="stylesheet" href="css/animation.css">
            <link rel="stylesheet" href="css/notification.css">
            
            <script src="js/assistant.js"></script>
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
        </head>
        <body>';

        self::renderSidebar();
        echo '<div class="main"><div class="dashboard">';

        match ($page) {
            'password' => self::password(),
            'backup' => self::backup(),
            'login' => self::login(),
            'restore' => self::restore(),
            'security' => self::security(),
            'language' => self::language(),
            default => self::home()
        };

        echo '</div></div></body></html>';
    }

    private static function renderSidebar()
    {
        echo '
        <div class="sidebar">
            <h2>' . lang::get("settings-btn") . '</h2>
            <ul>
                <li><a href="?page=password">🔐 ' . lang::get("password-field") . '</a></li>
                <li><a href="?page=backup">💾 النسخ الاحتياطي</a></li>
                <li><a href="?page=restore">♻️ استعادة النسخة</a></li>
                <li><a href="home.php">⬅️ ' . lang::get("home-btn") . '</a></li>
                <li><a href="?page=login">🔑 إعدادات الدخول</a></li>
                <li><a href="?page=security">🛡️ ' . lang::get("security") . '</a></li>
                <li><a href="?page=language">🌐 ' . lang::get("language-btn") . '</a></li>
            </ul>
        </div>';
    }


    public static function password()
    {
        echo '
        <div class="card">
            <h3>تغيير كلمة المرور</h3>
            <div class="form-group">
                <label for="old-password">كلمة المرور القديمة</label>
                <input type="password" id="old-password" placeholder="أدخل كلمة المرور القديمة">
            </div>
            <div class="form-group">
                <label for="new-password">كلمة المرور الجديدة</label>
                <input type="password" id="new-password" placeholder="أدخل كلمة المرور الجديدة">
            </div>
            <div class="form-group">
                <label for="confirm-password">تأكيد كلمة المرور الجديدة</label>
                <input type="password" id="confirm-password" placeholder="أعد كتابة كلمة المرور الجديدة">
            </div>
            <button class="save-btn" onclick="changePassword()">
                تحديث
            </button>
        </div>
        <script>
            function changePassword() {
                const oldPass = document.getElementById("old-password").value.trim();
                const newPass = document.getElementById("new-password").value.trim();
                const confirmPass = document.getElementById("confirm-password").value.trim();
    
                if (!oldPass || !newPass || !confirmPass) {
                    return Message("يرجى ملء جميع الحقول");
                }
                if (newPass !== confirmPass) {
                    return Message("كلمة المرور الجديدة غير متطابقة");
                }
    
                sendRequest({
                    type: "settings",
                    job: "change password",
                    old: oldPass,
                    new: newPass
                }).then((res) => {
                    if (res.status === "password changed") {
                        Message("تم تغيير كلمة المرور، سيتم تسجيل الخروج الآن");
                        setTimeout(() => {
                            location.href = "index.php";
                        }, 2000);
                    } else if (res.debug === "old password incorrect") {
                        Message("كلمة المرور القديمة غير صحيحة");
                    } else {
                        Message("حدث خطأ أثناء تحديث كلمة المرور");
                    }
                });
            }
        </script>
        ';
    }


    private static function backup()
    {
        echo '
    <div class="card">
        <h3>النسخ الاحتياطي</h3>
        <p>يمكنك تحميل نسخة احتياطية مشفرة من قاعدة البيانات.</p>
        <button class="save-btn" id="backup-btn">تحميل النسخة الاحتياطية</button>
        <script>
        document.querySelector("#backup-btn").onclick = async () => {
            Showindicator(document.body);
            const response = await sendRequest({ type: "settings", job: "backup" });
            indicatorRemover();

            if (response?.status === "ok" && response.backup) {
                const data = response.backup;
                const blob = new Blob([data], { type: "application/json;charset=utf-8" });
                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = "backup.json";
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
                showNotification(lang.get("notification-save"));
            } else if (response?.response === "invalid key") {
                showNotification(lang.get("invalid key"));
            } else if (response?.response === "no data") {
                showNotification(lang.get("no-data"));
            } else {
                showNotification(lang.get("unexpected-error"));
            }
        };
        </script>
    </div>';
    }

    private static function login()
    {
        echo '<div class="card">
            <h3>إعدادات الدخول</h3>
            <div class="form-group">
                <label>عدد محاولات تسجيل الدخول قبل الحظر</label>
                <input type="number" min="1" max="10">
            </div>
            <button class="save-btn">حفظ</button>
        </div>';
    }

    private static function security()
    {
        echo '<div class="card">
            <h3>إعدادات الأمان</h3>
            <div class="form-group">
                <label>تفعيل المصادقة الثنائية</label>
                <select>
                    <option>نعم</option>
                    <option>لا</option>
                </select>
            </div>
            <button class="save-btn">حفظ</button>
        </div>';
    }

    private static function language()
    {
        echo '<div class="card">
            <h3>اللغة</h3>
            <div class="form-group">
                <label>اختر اللغة</label>
                <select>
                    <option value="ar">العربية</option>
                    <option value="en">English</option>
                </select>
            </div>
            <button class="save-btn">تحديث</button>
        </div>';
    }

    private static function home()
    {
        $dbType = self::$connection->get_server_info();
        $phpVersion = phpversion();
        $systemOS = PHP_OS;
        $freeSpace = round(disk_free_space("/") / 1024 / 1024 / 1024, 2) . " GB";

        $buttonsCount = self::$connection->query("SELECT COUNT(*) AS count FROM buttons")->fetch_assoc()['count'] ?? 0;

        echo '
        <div class="card">
            <h3>معلومات النظام</h3>
            <ul>
                <li>🧠 نوع قاعدة البيانات: <b>' . $dbType . '</b></li>
                <li>🖥️ نظام التشغيل: <b>' . $systemOS . '</b></li>
                <li>🧩 إصدار PHP: <b>' . $phpVersion . '</b></li>
                <li>💾 المساحة الحرة: <b>' . $freeSpace . '</b></li>
                <li>🔘 عدد الأزرار: <b>' . $buttonsCount . '</b></li>
            </ul>
        </div>
        ';

        $result = self::$connection->query("SELECT username, ip_address, user_agent, created_at FROM auth_tokens ORDER BY id DESC LIMIT 10");
        $sessions = '';
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $uaInfo = self::parseUserAgent($row['user_agent']);
                $sessions .= "<tr>
                <td>{$row['username']}</td>
                <td>{$row['ip_address']}</td>
                <td>{$uaInfo}</td>
                <td>{$row['created_at']}</td>
            </tr>";
            }
        } else {
            $sessions = '<tr><td colspan="4">لا توجد جلسات متاحة</td></tr>';
        }

        echo '
        <div class="card" style="margin-top: 2rem;">
            <h3>آخر 10 جلسات دخول</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#eee;">
                            <th>المستخدم</th>
                            <th>IP</th>
                            <th>المتصفح</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $sessions . '
                    </tbody>
                </table>
            </div>
        </div>';
    }


    private static function parseUserAgent($agent)
    {
        $device = 'غير معروف';
        $os = 'غير معروف';

        if (preg_match('/mobile/i', $agent)) {
            $device = '📱 هاتف';
        } else {
            $device = '💻 حاسوب';
        }

        if (preg_match('/Windows NT/i', $agent)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $agent)) {
            $os = 'macOS';
        } elseif (preg_match('/Android/i', $agent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad/i', $agent)) {
            $os = 'iOS';
        } elseif (preg_match('/Linux/i', $agent)) {
            $os = 'Linux';
        }

        return "$device – $os";
    }

    private static function restore()
    {
        echo '
        <div class="card">
            <h3>استعادة النسخة الاحتياطية</h3>
            <p>قم بتحميل ملف النسخة الاحتياطية المشفر، مع إدخال كلمة المرور المستخدمة للتشفير.</p>
    
            <input type="password" id="restore-password" placeholder="🔑 كلمة المرور" class="input restore-pass" />
            <input type="file" id="restore-file" accept="application/json" class="input restore-file" />
            <button class="save-btn" id="restore-btn">استعادة النسخة</button>
    
            <script>
            document.querySelector("#restore-btn").onclick = async () => {
                const fileInput = document.querySelector("#restore-file");
                const passwordInput = document.querySelector("#restore-password");
    
                const file = fileInput.files[0];
                const password = passwordInput.value.trim();
    
                if (!file) {
                    showNotification("يرجى اختيار ملف النسخة الاحتياطية");
                    return;
                }
    
                if (!password) {
                    showNotification("يرجى إدخال كلمة المرور");
                    return;
                }
    
                Showindicator(document.body);
    
                const reader = new FileReader();
                reader.onload = async () => {
                    const fileContent = reader.result;
    
                    const response = await sendRequest({
                        type: "settings",
                        job: "restore",
                        key: password,       
                        backup: fileContent
                    });


                    indicatorRemover();
    
                    if (response?.data === "imported") {
                        showNotification(lang.get("تمت الاستعادة"));
                    } else {
                        showNotification(response?.response || "فشل الاستعادة");
                    }
                };
    
                reader.readAsText(file);
            };
            </script>
        </div>';
    }


    function __destruct()
    {
        if (self::$connection) self::$connection->close();
    }
}

new SettingsPage();