<?php





class settings

{

    use Tools;

    private static function Button_exist($button)
    {
        $encryptedButton = self::encryptText($button);
        $link = self::connectToDB();

        $stmt = $link->prepare("SELECT COUNT(*) as count FROM `buttons` WHERE `button` = ?");
        $stmt->bind_param("s", $encryptedButton);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $link->close();


        return $result['count'] > 0;
    }
    function __construct($post, $key, $link)
    {
        self::$encryptionKey = $key;
        self::$connection = $link;
        $job = $post['job'] ?? null;

        switch ($job) {

            case "change password":
                return self::ChangePassword($post, $link);
                break;
            case 'backup':
                return self::backup($post, $link);
                break;
            case 'restore':
                return self::ImportBackup($post, $link);
                break;
            case 'single backup':
                return self::BackupSingleButton($post, $link);
                break;
            case "update login settings":
                return self::LoginSettings($post, $link);
                break;
            case "get login settings":
                return self::GetLoginSettings($link);
                break;
            case "update language":
                return self::UpdateLanguage($post, $link);
                break;
            case "get language":
                return self::GetLanguage($link);
                break;
            case "change username":
                return self::ChangeUsername($post, $link);
                break;
            case "delete blocked ip":
                return self::deleteBlockedIp($post);
                break;
            case 'save telegram':
                return self::saveTelegramSettings($post, $link);
                break;
        }

        return new Response(400, ['debug' => 'Type not match']);
    }
    private static function ChangePassword($data, $link)
    {
        $Bearer = self::Headers();
        $username =  $Bearer['user'] ?? null;

        $old_password = $data['old'] ?? null;
        $new_password = $data['new'] ?? null;

        if (empty($username) || empty($new_password) || empty($old_password))
            return new Response(400, ['debug' => 'empty info']);

        $stmt = $link->prepare("SELECT `password` FROM `admin_info` WHERE `username` = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();


        if (!$result || !password_verify($old_password, $result['password']))
            return new Response(403, ['debug' => 'old password incorrect']);


        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $link->prepare("UPDATE `admin_info` SET `password` = ? WHERE `username` = ?");
        $stmt->bind_param("ss", $hashed, $username);
        $stmt->execute();
        $stmt->close();


        $stmt = $link->prepare("DELETE FROM `auth_tokens` WHERE `username` = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();


        return new Response(200, ['status' => 'password changed']);
    }
    private static function backup($data, $link)
    {

        $link = self::$connection;
        $stmt = $link->query("SELECT `button`, `unique_id`, `main`, `password`, `columns` FROM `buttons`");
        $buttons = [];
        while ($row = $stmt->fetch_assoc()) {

            $buttonName = self::decryptText($row['button']);
            $uniqueId = $row['unique_id'];
            $mainFields = json_decode(self::decryptText($row['main']), true) ?? [];
            $passwordFields = json_decode(self::decryptText($row['password']), true) ?? [];
            $columns = json_decode(self::decryptText($row['columns']), true) ?? [];
            $data = $link->query("SELECT * FROM `$uniqueId`");
            $records = [];

            while ($record = $data->fetch_assoc()) {
                $entry = ['id' => $record['id']];

                foreach ($columns as $col) {
                    $encCol = self::encryptText($col);
                    $rawValue = $record[$encCol] ?? 'empty';
                    $entry[$col] = $rawValue === 'empty' ? 'empty' : self::decryptText($rawValue);
                }

                $records[] = $entry;
            }

            $buttons[$buttonName] = [
                'columns' => $columns,
                'main' => $mainFields,
                'password' => $passwordFields,
                'data' => $records
            ];
        }



        $json = json_encode($buttons, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $encryptedJson = self::encryptText($json);
        $backup = json_encode(['iv' => self::$IV_KEY, 'data' => $encryptedJson], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return new Response(200, ['status' => 'ok', 'backup' => $backup]);
    }
    private static function ImportBackup($data, $link)
    {
        $backupData = $data['backup'] ?? null;
        if (!$backupData) return new Response(400, ['debug' => 'backup data missing']);

        $backupData = json_decode($backupData, true);
        $IV_KEY = $backupData['iv'] ?? null;
        if (!$IV_KEY) return new Response(400, ['debug' => 'iv missing']);
        $backupData = $backupData['data'] ?? null;
        if (!$backupData) return new Response(400, ['debug' => 'backup data missing']);

        $decryptionKey = $data['key'] ?? null;
        if (!$decryptionKey) return new Response(400, ['debug' => 'decryption key missing']);

        $decryptedJson = self::decryptText($backupData, $decryptionKey, $IV_KEY);
        $parsed = json_decode($decryptedJson, true);

        if (!$parsed || !is_array($parsed)) return new Response(400, ['debug' => 'invalid json']);

        foreach ($parsed as $buttonName => $info) {
            $columns = $info['columns'] ?? [];
            $main = $info['main'] ?? [];
            $password = $info['password'] ?? [];
            $dataRows = $info['data'] ?? [];

            if (!is_array($columns) || !is_array($dataRows)) continue;

            $encryptedButton = self::encryptText($buttonName);

            $count = null;
            $existsStmt = $link->prepare("SELECT COUNT(*) FROM `buttons` WHERE `button` = ?");
            $existsStmt->bind_param("s", $encryptedButton);
            $existsStmt->execute();
            $existsStmt->bind_result($count);
            $existsStmt->fetch();
            $existsStmt->close();


            if ($count == 0) {
                $allColumns = self::encryptText(json_encode($columns));
                $mainColumns = self::encryptText(json_encode($main));
                $passwordColumns = self::encryptText(json_encode($password));

                do {
                    $uniqueId = self::generateRandomString(rand(5, 22));
                    $result = $link->query("SELECT COUNT(*) as count FROM `buttons` WHERE `unique_id` = '$uniqueId'");
                    $exists = $result->fetch_assoc()['count'] > 0;
                    $tableCheck = $link->query("SHOW TABLES LIKE '$uniqueId'")->num_rows > 0;
                } while ($exists || $tableCheck);



                $stmt = $link->prepare("INSERT INTO `buttons` (`button`,`unique_id`,`main`,`password`,`columns`) VALUES (?, ?, ?, ?, ?)");

                $stmt->bind_param("sssss", $encryptedButton, $uniqueId, $mainColumns, $passwordColumns, $allColumns);

                $stmt->execute();

                $stmt->close();



                $link->query("CREATE TABLE `$uniqueId` ( id INT AUTO_INCREMENT PRIMARY KEY NOT NULL )");

                foreach ($columns as $col) {

                    $colEncrypted = self::encryptText($col);

                    $link->query("ALTER TABLE `$uniqueId` ADD `$colEncrypted` TEXT(99999) NOT NULL;");
                }
            } else {

                $stmt = $link->prepare("SELECT `unique_id` FROM `buttons` WHERE `button` = ?");

                $stmt->bind_param("s", $encryptedButton);

                $stmt->execute();

                $stmt->bind_result($uniqueId);

                $stmt->fetch();

                $stmt->close();
            }



            foreach ($dataRows as $row) {

                if (!is_array($row)) continue;



                $encryptedData = [];

                foreach ($columns as $col) {

                    $encCol = self::encryptText($col);

                    $val = $row[$col] ?? null;

                    $encVal = $val ? self::encryptText($val) : 'empty';

                    $encryptedData[$encCol] = $encVal;
                }



                $fields = array_keys($encryptedData);

                $values = array_values($encryptedData);

                $placeholders = implode(',', array_fill(0, count($fields), '?'));



                $stmt = $link->prepare("INSERT INTO `$uniqueId` (`" . implode('`,`', $fields) . "`) VALUES ($placeholders)");

                $stmt->bind_param(str_repeat('s', count($values)), ...$values);

                $stmt->execute();

                $stmt->close();
            }
        }



        return new Response(200, ['data' => 'imported']);
    }
    private static function BackupSingleButton($data, $link)
    {

        $button = $data['button'] ?? null;



        if (!$button) {

            return new Response(400, ['debug' => 'button name missing']);
        }



        if (!self::Button_exist($button)) {

            return new Response(404, ['debug' => 'button not found']);
        }



        $stmt = $link->prepare("SELECT `unique_id`, `main`, `password`, `columns` FROM `buttons` WHERE `button` = ?");

        $stmt->bind_param("s", self::encryptText($button));

        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        $stmt->close();



        if (!$row) {

            return new Response(404, ['debug' => 'button data missing']);
        }



        $unique = $row['unique_id'];

        $columns = json_decode(self::decryptText($row['columns']), true) ?? [];

        $main = json_decode(self::decryptText($row['main']), true) ?? [];

        $password = json_decode(self::decryptText($row['password']), true) ?? [];



        $query = $link->query("SELECT * FROM `$unique`");

        $dataRows = [];

        while ($entry = $query->fetch_assoc()) {

            $record = [];

            foreach ($entry as $key => $value) {

                if ($key === 'id') {

                    $record['id'] = $value;
                } else {

                    $decryptedKey = self::decryptText($key);

                    $record[$decryptedKey] = $value ? self::decryptText($value) : "";
                }
            }

            $dataRows[] = $record;
        }



        $finalJson = json_encode([

            $button => [

                'columns'  => $columns,

                'main'     => $main,

                'password' => $password,

                'data'     => $dataRows

            ]

        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);



        $encryptedJson = self::encryptText($finalJson);

        $backup = json_encode(['iv' => self::$IV_KEY, 'data' => $encryptedJson], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return new Response(200, ['status' => 'ok', 'backup' => $backup]);
    }
    private static function LoginSettings($data, $link)
    {

        $loginLimit = intval($data['login_limit']) ?? null;

        $blockTime = intval($data['block_time']) ?? null;



        if (!is_numeric($loginLimit) || !is_numeric($blockTime)) {

            return new Response(400, ['debug' => 'invalid input']);
        }

        $stmt = $link->prepare("UPDATE `setting` SET `times` = ?, `time` = ? WHERE `id` = 1");

        $stmt->bind_param("ii", $loginLimit, $blockTime);

        $stmt->execute();

        $stmt->close();



        return new Response(200, ['status' => 'updated']);
    }
    private static function GetLoginSettings($link)
    {

        $result = $link->query("SELECT `times`, `time` FROM `setting` WHERE `id` = 1");

        if ($result && $row = $result->fetch_assoc()) {

            return new Response(200, ['status' => 'ok', 'login_attempts' => $row['times'], 'block_time' => $row['time']]);
        }



        return new Response(500, ['debug' => 'cannot fetch login settings']);
    }
    private static function UpdateLanguage($data, $link)
    {

        $lang = $data['lang'] ?? null;

        if (!in_array($lang, ['ar', 'en'])) {

            return new Response(400, ['debug' => 'invalid lang']);
        }



        $stmt = $link->prepare("UPDATE `setting` SET `lang` = ? WHERE `id` = 1");

        $stmt->bind_param("s", $lang);

        $stmt->execute();

        $stmt->close();



        return new Response(200, ['status' => 'language updated']);
    }
    private static function GetLanguage($link)
    {

        $result = $link->query("SELECT `lang` FROM `setting` WHERE `id` = 1");

        if ($result && $row = $result->fetch_assoc()) {

            return new Response(200, ['status' => 'ok', 'lang' => $row['lang']]);
        }



        return new Response(500, ['debug' => 'cannot fetch language']);
    }
    private static function ChangeUsername($data, $link)
    {

        $Bearer = self::Headers();

        $currentUsername = $Bearer['user'] ?? null;

        $newUsername = $data['new'] ?? null;



        if (!$currentUsername || !$newUsername)

            return new Response(400, ['debug' => 'missing username info']);



        $check = $link->prepare("SELECT COUNT(*) as count FROM `admin_info` WHERE `username` = ?");

        $check->bind_param("s", $newUsername);

        $check->execute();

        $exists = $check->get_result()->fetch_assoc()['count'] > 0;

        $check->close();



        if ($exists) return new Response(200, ['response' => 'username exists']);



        $stmt = $link->prepare("UPDATE `admin_info` SET `username` = ? WHERE `username` = ?");

        $stmt->bind_param("ss", $newUsername, $currentUsername);

        $stmt->execute();

        $stmt->close();



        $link->query("DELETE FROM `auth_tokens` WHERE `username` = '$currentUsername'");



        return new Response(200, ['status' => 'username changed']);
    }
    private static function deleteBlockedIp($data)
    {

        $ip = $data['ip'] ?? null;

        if (!$ip) return new Response(400, ['debug' => 'empty ip']);



        $stmt = self::$connection->prepare("DELETE FROM `visitors` WHERE `ip` = ?");

        $stmt->bind_param("s", $ip);

        if ($stmt->execute()) {

            return new Response(200, ['status' => 'deleted']);
        } else {

            return new Response(500, ['debug' => 'failed to delete']);
        }
    }
    private static function saveTelegramSettings($data, $link)
    {

        $token = $data['token'] ?? null;

        $chatId = $data['chat_id'] ?? null;



        if (!$token || !$chatId) {

            return new Response(400, ['debug' => 'missing token or chat_id']);
        }



        $stmt = $link->prepare("UPDATE `admin_info` SET `api_token` = ?, `chat_id` = ? WHERE `id` = 1");

        $stmt->bind_param("ss", $token, $chatId);

        $stmt->execute();

        $stmt->close();



        return new Response(200, ['status' => 'saved']);
    }
}
