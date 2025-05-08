<?php

use LDAP\Result;

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

        if (!$result || !password_verify($old_password, $result['password'])) {
            return new Response(403, ['debug' => 'old password incorrect']);
        }

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

        return new Response(200, ['status' => 'ok', 'backup' => $encryptedJson]);
    }
    private static function ImportBackup($data, $link)
    {
        $backupData = $data['backup'] ?? null;
        if (!$backupData) return new Response(400, ['debug' => 'backup data missing']);

        $decryptionKey = $data['key'] ?? null;
        if (!$decryptionKey) return new Response(400, ['debug' => 'decryption key missing']);

        $decryptedJson = self::decryptText($backupData, $decryptionKey);
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
}