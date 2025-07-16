<?php


class queries
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
        try {
            self::$encryptionKey = $key;
            self::$connection = $link;

            $job = $post['job'] ?? null;
            $button = $post['button'] ?? null;

            if (!$job) return new Response(400, ['debug' => 'empty job']);

            return match ($job) {
                'buttons list'         => self::ButtonsList($link),
                'new button'           => self::NewButton($button, $post, $link),
                default                => self::routeButtonJob($job, $button, $post, $link)
            };
        } catch (Exception $e) {
            return new Response(500, ['debug' => $e->getMessage()]);
        }
    }

    private static function routeButtonJob($job, $button, $post, $link)
    {
        if (!self::Button_exist($button)) return new Response(200, ['response' => 'button not exist']);

        return match ($job) {
            'rename button'        => self::RenameButton($post, $link),
            'show Button'          => self::GetButton($button, $link),
            'delete button'        => self::DeleteButton($button, $link),
            'Get Columns'          => self::ButtonColumns($button, $link),
            'insert Data'          => self::insertData($post, $button, $link),
            'select id'            => self::SelectID($button, $post, $link),
            'delete id'            => self::DeleteID($post, $button, $link),
            'New Column'           => self::NewColumn($button, $post, $link),
            'update value'         => self::updateValue($button, $post, $link),
            'change type'          => self::ChangeType($button, $post, $link),
            'Button Columns Type'  => self::ButtonColumnsType($button, $link),
            'Rename Column'        => self::RenameColumn($button, $post, $link),
            'Column Validation'    => self::ColumnValidation($button, $post, $link),
            'Delete Column'        => self::DeleteColumn($button, $post, $link),
            default                => new Response(400, ['debug' => 'no job found'])
        };
    }
    private static function NewButton($button, $input_data, $link)
    {
        try {

            if (self::Button_exist($button))  return new Response(200, ['response' => 'Button exist']);

            $columns = (isset($input_data['columns']) && is_array($input_data['columns'])) ? $input_data['columns'] : [];
            $encrypted_button = self::encryptText($button);
            $mainColumns      = self::encryptText(json_encode($input_data['main'] ?? []));
            $passwordColumns  = self::encryptText(json_encode($input_data['password'] ?? []));
            $all_columns      = self::encryptText(json_encode($columns));

            do {
                $unique_id = self::generateRandomString(rand(5, 22));
                $link = new mysqli(self::$Serverip, self::$ServerUser, self::$ServerPassword, self::$database);
                $stmt = $link->prepare("SELECT COUNT(*) as count FROM `buttons` WHERE `unique_id` = ?");
                $stmt->bind_param("s", $unique_id);
                $stmt->execute();
                $exists = $stmt->get_result()->fetch_assoc()['count'] > 0;
                $stmt->close();
                $tableCheck = $link->query("SHOW TABLES LIKE '$unique_id'")->num_rows > 0;
            } while ($exists || $tableCheck);

            $stmt = $link->prepare("INSERT INTO `buttons` (`button`,`unique_id`,`main`,`password`,`columns`) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $encrypted_button, $unique_id, $mainColumns, $passwordColumns, $all_columns);
            $stmt->execute();
            $stmt->close();

            $link->query("CREATE TABLE `$unique_id` ( id INT AUTO_INCREMENT PRIMARY KEY NOT NULL )");
            foreach ($columns as $column) {
                $col = self::encryptText($column);
                $link->query("ALTER TABLE `$unique_id` ADD `$col` TEXT(99999) NOT NULL;");
            }

            return new Response(200, ['response' => 'successful']);
        } catch (Exception $e) {
            return new Response(500, ['debug' =>  $e->getMessage()]);
        }
    }
    private static function ButtonsList($link)
    {
            $buttons = [];
            $queryResult = @$link->query("SELECT `button` FROM `buttons` ORDER by `id`");
            if ($queryResult->num_rows > 0) {
                while ($buttonData = $queryResult->fetch_assoc()) {
                    $decryptedButton = self::decryptText($buttonData['button']);
                    $decodedButton = htmlspecialchars_decode(htmlspecialchars($decryptedButton));
                    array_push($buttons, $decodedButton);
                }
                return new Response(200, ['response' => 'ok', 'buttons' => $buttons]);
            } else
                return new Response(200, ['response' => 'empty']);
        }
        private static function DeleteButton($button, $link)
        {
            $button = self::encryptText($button);
            $uniqueid = $link->query("SELECT `unique_id` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc()['unique_id'];
            $link->query("DELETE FROM `buttons` WHERE `button` = '$button'");
            $link->query("DROP TABLE IF EXISTS " . $uniqueid);
            return new Response(200);
        }
        private static function RenameButton($data, $link)
        {
            $newName = $data['new'] ?? null;
            $oldName = $data['button'] ?? null;

            if (!$newName)
                return new Response(400, ['debug' => 'new name not set']);

            if (self::Button_exist($newName))
                return new Response(200, ['response' => 'new button exist']);


            $newEncrypted = self::encryptText($newName);
            $oldEncrypted = self::encryptText($oldName);

            $stmt = $link->prepare("UPDATE `buttons` SET `button` = ? WHERE `button` = ?");
            $stmt->bind_param("ss", $newEncrypted, $oldEncrypted);
            if ($stmt->execute()) {
                return new Response(200, ['response' => 'ok']);
            } else {
                return new Response(500, ['debug' => 'failed to rename button']);
            }
    }
    private static function GetButton($button, $link)
    {
        $encryptedButton = self::encryptText($button);

        $stmt = $link->prepare("SELECT `unique_id`, `main`, `password`, `columns` FROM `buttons` WHERE `button` = ?");
        $stmt->bind_param("s", $encryptedButton);
        $stmt->execute();
        $buttonData = $stmt->get_result()->fetch_assoc();

        if (!$buttonData) {
            return new Response(404, ['debug' => 'button not found']);
        }

        $uniqueId = $buttonData['unique_id'];
        $mainFields = json_decode(self::decryptText($buttonData['main']), true) ?? [];
        $passwordFields = json_decode(self::decryptText($buttonData['password']), true) ?? [];
        $columns = json_decode(self::decryptText($buttonData['columns']), true);

        if (empty($columns)) {
            return new Response(200, ['response' => 'no columns']);
        }

        $result = $link->query("SELECT * FROM `$uniqueId`");
        if (!$result || $result->num_rows < 1) {
            return new Response(200, ['response' => 'no data']);
        }

        $finalData = [];
        while ($row = $result->fetch_assoc()) {
            $entry = ['id' => $row['id']];

            $entry['main'] = self::extractAndDecrypt($row, $mainFields);
            $entry['passwords'] = self::extractAndDecrypt($row, $passwordFields);

            $finalData[] = $entry;
        }

        return new Response(200, ['data' => $finalData]);
    }
    private static function extractAndDecrypt(array $row, array $fields): array|string
    {
        if (empty($fields)) return 'empty';

        $data = [];
        foreach ($fields as $field) {
            $encField = self::encryptText($field);
            $value = $row[$encField] ?? null;
            $data[$field] = $value ? self::decryptText($value) : 'empty';
        }
        return $data;
    }
    private static function ButtonColumns($button, $link)
    {
        try {
            $button = self::encryptText($button);
            $buttonData = $link->query("SELECT `columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_array(MYSQLI_ASSOC);
            $columns = json_decode(self::decryptText($buttonData['columns']), true) ?? null;
            if (count($columns) > 0) {
                return new Response(200, ['columns' => $columns]);
            } else return new Response(200, ['columns' => 'no columns']);
        } catch (Exception $e) {
            return new Response(500, ['debug' =>  $e->getMessage()]);
        }
    }
    private static function insertData($data, $button, $link)
    {
        $info = $data['info'] ?? null;
        if (!$info) return new Response(400, ['debug' => 'empty info']);

        $button = self::encryptText($button);
        $stmt = $link->prepare("SELECT `unique_id`, `columns` FROM `buttons` WHERE `button` = ?");
        $stmt->bind_param("s", $button);
        $stmt->execute();
        $buttonData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $uniqueid = $buttonData['unique_id'];
        $columns = json_decode(self::decryptText($buttonData['columns']), true);

        if (array_diff($columns, array_keys($info)) || array_diff(array_keys($info), $columns)) {
            return new Response(400, ['debug' => "Columns don't match"]);
        }

        $encrypted_data = [];
        foreach ($info as $key => $value) {
            $col = self::encryptText($key);
            $val = $value ? self::encryptText($value) : 'empty';
            $encrypted_data[$col] = $val;
        }

        $fields = array_keys($encrypted_data);
        $values = array_values($encrypted_data);
        $placeholders = implode(",", array_fill(0, count($fields), "?"));

        $stmt = $link->prepare("INSERT INTO `$uniqueid` (`" . implode("`,`", $fields) . "`) VALUES ($placeholders)");
        $stmt->bind_param(str_repeat("s", count($values)), ...$values);
        $stmt->execute();
        $stmt->close();

        return new Response(200);
    }
    private static function SelectID($button, $data, $link)
    {
        $id = @$data['id'] ?? null;
        if (!$id) return new Response(400, ['debug' => 'empty id']);
        if (!is_numeric($id)) return new Response(400, ['debug' => 'invalid id']);
        $button = self::encryptText($button);
        $buttonData = $link->query("SELECT `unique_id`,`columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc();
        $uniqueid = $buttonData['unique_id'];
        $columns = json_decode(self::decryptText($buttonData['columns']), true);
        if (empty($columns))
            return new Response(200, ['response' => 'no columns']);
        $result  = $link->query("SELECT * FROM  `" . $uniqueid . "` WHERE `id` = $id")->fetch_array(MYSQLI_ASSOC);
        $data = array();
        foreach ($result as $key => $value) {
            if ($key != 'id')
                $data[self::decryptText($key)] = !empty($value) ? self::decryptText($value) : 'empty';
            else
                $data['id'] =  $value;
        }
        return new Response(200, ["data" => $data]);
    }
    private static function DeleteID($data, $button, $link)
    {
        $id = @$data['id'] ?? null;
        if (!$id) return new Response(400, ['debug' => 'empty id']);
        if (!is_numeric($id)) return new Response(400, ['debug' => 'invalid id']);
        $button = self::encryptText($button);
        $uniqueid = $link->query("SELECT `unique_id` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc()['unique_id'];
        $stmt = $link->prepare("DELETE FROM `$uniqueid` WHERE `id` = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return new Response(200);
    }
    private static function NewColumn($button, $data, $link)
    {
        $column = @$data['column'] ?? null;
        if (!$column) return new Response(200, ['debug' => 'no column set']);
        $type = $data['FieldType'] ?? 'normal';

        $button = self::encryptText($button);
        $buttonData = $link->query("SELECT `main`,`password`,`unique_id`,`columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc();
        $columns = json_decode(self::decryptText($buttonData['columns']), true);
        $uniqueid = $buttonData['unique_id'];
        $sqlMains = json_decode(self::decryptText($buttonData['main']), true) ?? array();
        $sqlPasswords = json_decode(queries::decryptText($buttonData['password']), true) ?? array();

        if (in_array($column, $columns)) return new Response(200, ['response' => 'column duplicated']);
        $columns = self::Additem($columns, $column);
        $columns = self::encryptText(json_encode($columns));
        $link->query("UPDATE `buttons` SET `columns` = '" . $columns . "' WHERE `button` = '$button'");
        $link->query("ALTER TABLE `" . $uniqueid . "` ADD COLUMN `" . queries::encryptText($column) . "` TEXT(99999)");
        switch ($type) {
            case 'main':
                if (!in_array($column, $sqlMains)) array_push($sqlMains, $column);
                $new = self::encryptText(json_encode(array_values($sqlMains)));
                $link->query("UPDATE `buttons` SET `main` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                break;
            case 'password':
                if (!in_array($column, $sqlPasswords)) array_push($sqlPasswords, $column);
                $new = self::encryptText(json_encode(array_values($sqlPasswords)));
                $link->query("UPDATE `buttons` SET `password` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                break;
        }
        return new Response(200);
    }
    private static function updateValue($button, $data, $link)
    {
        $id = @$data['id'] ?? null;
        $column = @$data['column'] ?? null;
        $value = @$data['value'] ?? null;
        if (!$id) return new Response(400, ['debug' => 'empty id']);
        if (!is_numeric($id)) return new Response(400, ['debug' => 'invalid id']);
        if (!$column) return new Response(400, ['debug' => 'empty column']);
        if (!$value) return new Response(400, ['debug ' => 'empty value']);

        $button = self::encryptText($button);
        $value  = self::encryptText($value);
        $buttonData = $link->query("SELECT `unique_id`,`columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc();
        $uniqueid = $buttonData['unique_id'];
        $columns = json_decode(self::decryptText($buttonData['columns']), true);
        if (!in_array($column, $columns)) return new Response(400, ['debug' => 'column not found']);
        $column = self::encryptText($column);
        $link->query("UPDATE `" . $uniqueid . "` SET `" . $column . "` = '" . $value . "'  WHERE `id` = " . $id);
        return new Response(200);
    }
    private static function ChangeType($button, $data, $link)
    {
        $column = @$data['column'] ?? null;
        $type = @$data['FieldType'] ?? null;
        if (!$column) return new Response(400, ['debug' => 'empty column']);
        if (!$type) return new Response(400, ['debug ' => 'empty value']);

        $button = self::encryptText($button);
        $buttonData = $link->query("SELECT `main`,`password`,`columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc();
        $columns = json_decode(self::decryptText($buttonData['columns']), true);
        $sqlMains = json_decode(self::decryptText($buttonData['main']), true) ?? array();
        $sqlPasswords = json_decode(queries::decryptText($buttonData['password']), true) ?? array();

        if (!in_array($column, $columns)) return new Response(400, ['debug' => 'column not found']);

        switch ($type) {
            case 'main':
                if (!in_array($column, $sqlMains)) {
                    array_push($sqlMains, $column);
                    $new = self::encryptText(json_encode(array_values($sqlMains)));
                    $link->query("UPDATE `buttons` SET `main` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                }
                if (in_array($column, $sqlPasswords)) {
                    $itemkey = array_search($column, $sqlPasswords);
                    unset($sqlPasswords[$itemkey]);
                    $new = self::encryptText(json_encode($sqlPasswords));
                    $link->query("UPDATE `buttons` SET `password` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                }
                break;
            case 'password':
                if (!in_array($column, $sqlPasswords)) {
                    array_push($sqlPasswords, $column);
                    $new = self::encryptText(json_encode(array_values($sqlPasswords)));
                    $link->query("UPDATE `buttons` SET `password` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                }
                if (in_array($column, $sqlMains)) {
                    $itemkey = array_search($column, $sqlMains);
                    unset($sqlMains[$itemkey]);
                    $new = self::encryptText(json_encode(array_values($sqlMains)));
                    $link->query("UPDATE `buttons` SET `main` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                }
                break;
            case 'normal':
                if (in_array($column, $sqlPasswords)) {
                    $itemkey = array_search($column, $sqlPasswords);
                    unset($sqlPasswords[$itemkey]);
                    $new = self::encryptText(json_encode(array_values($sqlPasswords)));
                    $link->query("UPDATE `buttons` SET `password` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                }
                if (in_array($column, $sqlMains)) {
                    $itemkey = array_search($column, $sqlMains);
                    unset($sqlMains[$itemkey]);
                    $new = self::encryptText(json_encode(array_values($sqlMains)));
                    $link->query("UPDATE `buttons` SET `main` = '" . $new . "' WHERE `buttons`.`button` = '" . $button . "'");
                }
                break;
        }
        return new Response(200);
    }
    private static function ButtonColumnsType($button, $link)
    {
        /** this function use for settings */
        $button = self::encryptText($button);
        $buttonData = @$link->query("SELECT `main`,`password`,`columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_array();
        $columns = json_decode(self::decryptText($buttonData['columns']), true) ?? null;
        $sqlMains = json_decode(self::decryptText($buttonData['main']), true) ?? null;
        $sqlPasswords = json_decode(queries::decryptText($buttonData['password']), true) ?? null;
        $Allcolumns = [];
        if ($sqlMains)     foreach ($sqlMains as $column) $Allcolumns[$column]     = 'main';
        if ($sqlPasswords) foreach ($sqlPasswords as $column) $Allcolumns[$column] = 'password';
        if ($columns) foreach ($columns as $column) {
            if (!in_array($column, $sqlMains) && !in_array($column, $sqlPasswords)) $Allcolumns[$column] = 'normal';
        }
        new Response(200, ['columns' => $Allcolumns]);
    }
    private static function RenameColumn($button, $data, $link)
    {
        $column = @$data['column'] ?? null;
        $new = @$data['new'] ?? null;
        if (!$column) return new Response(400, ['debug' => 'empty column']);
        if (!$new) return new Response(400, ['debug ' => 'empty new']);

        $button = self::encryptText($button);
        $buttonData = $link->query("SELECT `main`,`password`,`columns`,`unique_id` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc();
        $columns = json_decode(self::decryptText($buttonData['columns']), true) ?? array();
        $sqlMains = json_decode(self::decryptText($buttonData['main']), true) ?? array();
        $sqlPasswords = json_decode(self::decryptText($buttonData['password']), true) ?? array();

        if (!in_array($column, $columns)) return new Response(400, ['debug' => 'column not found']);
        if (in_array($new, $columns)) return new Response(200, ['response' => 'column duplicated']);
        $uniqueid = $buttonData['unique_id'];

        switch (true) {
            case in_array($column, $sqlMains):
                $newMains = self::replace_item($sqlMains, $column, $new);
                $newMains = self::encryptText(json_encode($newMains));
                $link->query("UPDATE `buttons` SET `main` = '" . $newMains . "' WHERE `buttons`.`button` = '" . $button . "'");
                break;
            case in_array($column, $sqlPasswords):
                $sqlPasswords = self::replace_item($sqlPasswords, $column, $new);
                $newPasswords = self::encryptText(json_encode($sqlPasswords));
                $link->query("UPDATE `buttons` SET `password` = '" . $newPasswords . "' WHERE `buttons`.`button` = '" . $button . "'");
                break;
        }
        $columns = self::replace_item($columns, $column, $new);
        $newcolumns = self::encryptText(json_encode(array_values($columns)));
        $link->query("UPDATE `buttons` SET `columns` = '" . $newcolumns . "' WHERE `button` = '$button'");
        $link->query("ALTER TABLE `" . $uniqueid . "` CHANGE `" . self::encryptText($column) . "` `" . self::encryptText($new) . "` TEXT");
        return new Response(200);
    }
    private static function ColumnValidation($button, $input_data, $link)
    {
        $column = $input_data['column'];
        if (empty($column)) return new Response(400, ['debug' => 'empty column']);
        $encryptedButtonName = self::encryptText($button);
        $Data = $link->query("SELECT `columns` FROM `buttons` WHERE `button` = '" . $encryptedButtonName . "'")->fetch_array();
        $columns = json_decode(self::decryptText($Data['columns']), true) ?? array();
        $status = in_array($column, $columns) ? 'found' : 'not found';
        return new Response(200, ['status' => $status]);
    }
    private static function DeleteColumn($button, $data, $link)
    {
        $column = @$data['column'] ?? null;
        if (!$column) return new Response(400, ['debug' => 'empty column']);

        $button = self::encryptText($button);
        $buttonData = $link->query("SELECT `main`, `password`, `columns`, `unique_id` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc();
        $columns = json_decode(self::decryptText($buttonData['columns']), true) ?? [];
        $sqlMains = json_decode(self::decryptText($buttonData['main']), true) ?? [];
        $sqlPasswords = json_decode(self::decryptText($buttonData['password']), true) ?? [];
        $uniqueid = $buttonData['unique_id'];

        if (!in_array($column, $columns)) return new Response(400, ['debug' => 'column not found']);

        $encCol = self::encryptText($column);
        $link->query("ALTER TABLE `$uniqueid` DROP COLUMN `$encCol`");

        $columns = array_values(array_filter($columns, fn($c) => $c !== $column));
        $sqlMains = array_values(array_filter($sqlMains, fn($c) => $c !== $column));
        $sqlPasswords = array_values(array_filter($sqlPasswords, fn($c) => $c !== $column));

        $link->query("UPDATE `buttons` SET 
        `columns` = '" . self::encryptText(json_encode($columns)) . "',
        `main` = '" . self::encryptText(json_encode($sqlMains)) . "',
        `password` = '" . self::encryptText(json_encode($sqlPasswords)) . "'
        WHERE `button` = '$button'");
        return new Response(200, ['status' => 'successful']);
    }
}