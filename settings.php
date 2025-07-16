<?php
error_reporting(0);
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
            'username' => self::username(),
            'backup-single' =>    self::backupSingleButton(),
            'telegram' => self::telegramBackup(),
            default => self::home()
        };

        echo '</div></div></body></html>';
    }
    private static function renderSidebar()
    {
        echo '
            <div class="sidebar">
                <h2>الإعدادات</h2>
                <ul>
                <li><a href="home.php">🏠 العودة للرئيسية</a></li>
                    <li><a href="settings.php?page=password">🔐 كلمة المرور</a></li>
                    <li><a href="settings.php?page=username">👤 تغيير اسم المستخدم</a></li>
                    <li><a href="settings.php?page=backup">💾 النسخ الاحتياطي</a></li>
                    <li><a href="settings.php?page=backup-single">🧩 نسخ زر واحد</a></li>
                    <li><a href="settings.php?page=restore">♻️ الاستعادة</a></li>
                    <li><a href="settings.php?page=login">🔑 إعدادات الدخول</a></li>
                    <li><a href="settings.php?page=security">🛡️ الأمان</a></li>
                    <li><a href="settings.php?page=language">🌐 اللغة</a></li>
                    <li><a href="settings.php?page=telegram">📤 تلكرام باك أب</a></li>
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
        echo '
            <div class="card">
                <h3>إعدادات الدخول</h3>
                <div class="form-group">
                    <label>عدد المحاولات قبل الحظر</label>
                    <input type="number" id="login-limit" min="1">
                </div>
                <div class="form-group">
                    <label>مدة الحظر بالدقائق</label>
                    <input type="number" id="block-time" min="1">
                </div>
                <button class="save-btn" onclick="saveLoginSettings()">حفظ الإعدادات</button>
            </div>

            <script>
            async function fetchLoginSettings() {
                const res = await sendRequest({ type: "settings", job: "get login settings" });
                if (res?.status === "ok") {
                    document.getElementById("login-limit").value = res.login_attempts;
                    document.getElementById("block-time").value = res.block_time;
                }
            }

            async function saveLoginSettings() {
                const limit = parseInt(document.getElementById("login-limit").value);
                const time = parseInt(document.getElementById("block-time").value);
                const res = await sendRequest({
                    type: "settings",
                    job: "update login settings",
                    login_limit: limit,
                    block_time: time
                });
                if (res?.status === "updated") {
                    showNotification(lang.get("notification-update"));
                }
            }

            fetchLoginSettings();
            </script>';
    }
    private static function security()
    {
        $link = self::connectToDB();

        $settingResult = $link->query("SELECT `times` FROM `setting` WHERE `id` = 1");
        if ($settingResult && $row = $settingResult->fetch_assoc()) {
            $maxAttempts = (int) $row['times'];
        } else {
            $maxAttempts = 3;
        }

        $blockedIps = [];
        $ipQuery = $link->query("SELECT `ip`, `times`, `time` FROM `visitors` WHERE `times` >= $maxAttempts ORDER BY `time` DESC");
        if ($ipQuery) {
            while ($row = $ipQuery->fetch_assoc()) {
                $blockedIps[] = $row;
            }
        }

        echo '
            <div class="card">
                <h3>قائمة العناوين المحظورة</h3>
                <p>العناوين التالية تم حظرها بسبب تجاوز عدد المحاولات المسموح به (' . $maxAttempts . ' مرات).</p>';

        if (empty($blockedIps)) {
            echo '<p style="color: gray;">لا توجد عناوين محظورة حالياً.</p>';
        } else {
            echo '
                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                    <thead>
                        <tr style="background-color: #eee;">
                            <th style="padding: 8px; border: 1px solid #ccc;">عنوان IP</th>
                            <th style="padding: 8px; border: 1px solid #ccc;">عدد المحاولات</th>
                            <th style="padding: 8px; border: 1px solid #ccc;">آخر محاولة</th>
                            <th style="padding: 8px; border: 1px solid #ccc;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($blockedIps as $row) {
                $ip = htmlspecialchars($row['ip']);
                echo '
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ccc;">' . $ip . '</td>
                            <td style="padding: 8px; border: 1px solid #ccc;">' . (int) $row['times'] . '</td>
                            <td style="padding: 8px; border: 1px solid #ccc;">' . htmlspecialchars($row['time']) . '</td>
                            <td style="padding: 8px; border: 1px solid #ccc;">
                                <button onclick="deleteBlockedIP(\'' . $ip . '\')" style="padding: 6px 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">🗑️ حذف</button>
                            </td>
                        </tr>';
            }
            echo '
                    </tbody>
                </table>';
        }

        echo <<<SCRIPT
                                <script>
                                function deleteBlockedIP(ip) {
                                    if (!confirm("هل أنت متأكد من حذف هذا العنوان؟")) return;

                                    sendRequest({
                                        type: "settings",
                                        job: "delete blocked ip",
                                        ip: ip
                                    }).then(res => {
                                        if (res?.status === "deleted") {
                                            showNotification("تم الحذف بنجاح");
                                            setTimeout(() => location.reload(), 800);
                                        } else {
                                            showNotification("فشل الحذف");
                                        }
                                    });
                                }
                                </script>
                                </div>
                                SCRIPT;
    }
    private static function language()
    {
        echo '
            <div class="card">
                <h3>تغيير اللغة</h3>
                <div class="form-group">
                    <label>اختر اللغة</label>
                    <select id="lang-select">
                        <option value="ar">العربية</option>
                        <option value="en">English</option>
                    </select>
                </div>
                <button class="save-btn" onclick="changeLanguage()">تحديث اللغة</button>
            </div>

            <script>
            async function fetchLang() {
                const res = await sendRequest({ type: "settings", job: "get language" });
                if (res?.status === "ok") {
                    document.getElementById("lang-select").value = res.lang;
                }
            }

            async function changeLanguage() {
                const selectedLang = document.getElementById("lang-select").value;
                const res = await sendRequest({
                    type: "settings",
                    job: "update language",
                    lang: selectedLang
                });
                if (res?.status === "language updated") {
                    showNotification(lang.get("language-update"));
                }
            }

            fetchLang();
            </script>';
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
    private static function backupSingleButton()
    {
        echo '
        <div class="card">
            <h3>نسخ احتياطي لزر معين</h3>
            <p>اختر الزر الذي تريد أخذ نسخة احتياطية له.</p>
    
            <select id="backup-single-select" class="input" style="margin-bottom: 1rem;"></select>
            <button class="save-btn" id="backup-single-btn">تحميل النسخة</button>
    
            <script>
            async function fetchButtons() {
                Showindicator(document.body);
                const response = await sendRequest({ type: "queries", job: "buttons list" });
                indicatorRemover();
    
                const select = document.querySelector("#backup-single-select");
                if (response?.response === "ok") {
                    response.buttons.forEach(btn => {
                        const option = document.createElement("option");
                        option.value = btn;
                        option.innerText = btn;
                        select.appendChild(option);
                    });
                } else {
                    const opt = document.createElement("option");
                    opt.disabled = true;
                    opt.innerText = lang.get("no-info");
                    select.appendChild(opt);
                }
            }
    
            document.querySelector("#backup-single-btn").onclick = async () => {
                const selected = document.querySelector("#backup-single-select").value;
                if (!selected) {
                    showNotification("يرجى اختيار زر");
                    return;
                }
    
                Showindicator(document.body);
                const response = await sendRequest({
                    type: "settings",
                    job: "single backup",
                    button: selected
                });
                indicatorRemover();
    
                if (response?.status == "ok") {
                    const data = response.backup;
                    const blob = new Blob([data], { type: "application/json" });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = `${selected}_backup.json`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);
                    showNotification(lang.get("notification-save"));
                } else {
                    showNotification(lang.get("invalid key"));
                }
            };
    
            fetchButtons();
            </script>
        </div>';
    }
    private static function username()
    {
        echo '
                <div class="card">
                    <h3>تغيير اسم المستخدم</h3>
                    <p>قم بإدخال اسم المستخدم الجديد، سيتم تسجيل الخروج تلقائياً بعد التغيير.</p>

                    <input type="text" id="new-username" class="input" placeholder="👤 اسم المستخدم الجديد" />
                    <button class="save-btn" id="change-username-btn">تغيير</button>

                    <script>
                    document.querySelector("#change-username-btn").onclick = async () => {
                        const input = document.querySelector("#new-username");
                        const newUsername = input.value.trim();

                        if (!newUsername) {
                            showNotification("يرجى إدخال اسم المستخدم");
                            return;
                        }

                        Showindicator(document.body);

                        const response = await sendRequest({
                            type: "settings",
                            job: "change username",
                            new: newUsername
                        });

                        indicatorRemover();

                        if (response?.status === "username changed") {
                            showNotification("تم تغيير اسم المستخدم. سيتم تسجيل الخروج...");
                            setTimeout(() => window.location = "index.php", 1500);
                        } else if (response?.response === "username exists") {
                            showNotification("اسم المستخدم موجود مسبقاً");
                        } else {
                            showNotification(response?.debug || "حدث خطأ أثناء تغيير الاسم");
                        }
                    };
                    </script>
                </div>';
    }
    private static function telegramBackup()
    {
        $token = null;
        $chatId = null;
        $stmt = self::$connection->prepare("SELECT  `api_token`, `chat_id` FROM `admin_info` LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($token, $chatId);
        $stmt->fetch();
        $stmt->close();

        echo '
                <div class="card">
                    <h3>إعدادات التلغرام</h3>
                    <div class="form-group">
                        <label for="tg-token">🔑 API Token</label>
                        <input type="text" id="tg-token" placeholder="ضع توكن البوت هنا" value="' . htmlspecialchars($token ?? '') . '">
                    </div>
                    <div class="form-group">
                        <label for="tg-chat">📩 Chat ID</label>
                        <input type="text" id="tg-chat" placeholder="ضع رقم Chat ID هنا" value="' . htmlspecialchars($chatId ?? '') . '">
                    </div>

                    <button class="save-btn" onclick="saveTelegramSettings()">💾 حفظ الإعدادات</button>
                    <button class="save-btn" onclick="testTelegram()">🧪 إرسال تجربة</button>
                    <button class="save-btn" onclick="sendBackupToTelegram()">📤 إرسال النسخة الاحتياطية</button>

                    <script>
                        async function saveTelegramSettings() {
                            const token = document.querySelector("#tg-token").value.trim();
                            const chatId = document.querySelector("#tg-chat").value.trim();

                            if (!token || !chatId) {
                                showNotification("لا تترك حقل فارغ");
                                return;
                            }

                            const res = await sendRequest({
                                type: "settings",
                                job: "save telegram",
                                token: token,
                                chat_id: chatId
                            });

                            if (res?.status === "saved") {
                                showNotification("تم حفظ الإعدادات");
                            } else {
                                showNotification("فشل الحفظ");
                            }
                        }

                        async function testTelegram() {
                            const token = document.querySelector("#tg-token").value.trim();
                            const chatId = document.querySelector("#tg-chat").value.trim();
                            if (!token || !chatId) return showNotification("يرجى تعبئة التوكن و الشات آي دي");

                            const res = await fetch(`https://api.telegram.org/bot${token}/sendMessage`, {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ chat_id: chatId, text: "✅ تم الاتصال بنجاح" })
                            });

                            if (res.ok) showNotification("تم الاتصال بنجاح");
                            else showNotification("فشل الاتصال بالتلغرام");
                        }

                        async function sendBackupToTelegram() {
                            const token = document.querySelector("#tg-token").value.trim();
                            const chatId = document.querySelector("#tg-chat").value.trim();
                            if (!token || !chatId) return showNotification("يرجى تعبئة التوكن و الشات آي دي");

                            Showindicator(document.body);
                            const response = await sendRequest({ type: "settings", job: "backup" });
                            indicatorRemover();

                            if (response?.status !== "ok") return showNotification("فشل في إنشاء النسخة الاحتياطية");

                            const blob = new Blob([response.backup], { type: "application/json" });
                            const formData = new FormData();
                            formData.append("chat_id", chatId);
                            formData.append("document", blob, "backup.json");

                            const tgRes = await fetch(`https://api.telegram.org/bot${token}/sendDocument`, {
                                method: "POST",
                                body: formData
                            });

                            if (tgRes.ok) showNotification("تم إرسال النسخة");
                            else showNotification("فشل إرسال النسخة");
                        }
                    </script>
                </div>';
    }
    function __destruct()
    {
        if (self::$connection) self::$connection->close();
    }
}

new SettingsPage();
