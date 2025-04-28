<?php


class queries
{
    use Tools;
    private static function Button_exist($button)
    {
        $buttonlist = [];
        $link = self::connectToDB();
        $sqlButtons = $link->query("SELECT * FROM `buttons`");
        $link->close();
        if ($sqlButtons->num_rows > 0) {
            while ($sqlButton = $sqlButtons->fetch_assoc()) {
                $decodedButton = htmlspecialchars_decode(htmlspecialchars(self::decryptText($sqlButton['button'])));
                array_push($buttonlist, $decodedButton);
            }
            return in_array($button, $buttonlist);
        } else
            return false;
    }

    function __construct($post, $key, $link)
    {
        try {
            $job = $post['job'] ?? null;
            self::$encryptionKey = $key;
            $button = $post['button'] ?? null;
            if (empty($job)) return new Response(400, ['debug' => 'empty job']);
            self::$connection = $link;

            switch ($job) {
                case  'buttons list':
                    return self::ButtonsList(self::$connection);
                    break;
                case 'new button':
                    return self::NewButton($button, $post, self::$connection);
                    break;
            }


            if (!self::Button_exist($button)) return new Response(200, ['response' => 'button not exist']);

            switch ($job) {
                case 'rename button':
                    return self::RenameButton($post, self::$connection);
                case !self::Button_exist($button):
                    return new Response(200, ['response' => 'button not exist']);
                case 'show Button':
                    return self::GetButton($button, self::$connection);
                case 'delete button':
                    return self::DeleteButton($button, self::$connection);
                case 'Get Columns':
                    return self::ButtonColumns($button, self::$connection);
                case 'insert Data':
                    return self::insertData($post, $button, self::$connection);
                case 'select id':
                    return self::SelectID($button, $post, self::$connection);
                case 'delete id':
                    return self::DeleteID($post, $button, self::$connection);
                case 'New Column':
                    return self::NewColumn($button, $post, self::$connection);
                case 'update value':
                    return self::updateValue($button, $post, self::$connection);
                case 'change type':
                    return self::ChangeType($button, $post, self::$connection);
                case 'Button Columns Type':
                    return self::ButtonColumnsType($button, self::$connection);
                case 'Rename Column':
                    return self::RenameColumn($button, $post, self::$connection);
                case 'Column Validation':
                    return self::ColumnValidation($button, $post, self::$connection);
                default:
                    return new Response(400, ['debug' => 'no job found']);
            }
        } catch (Exception $exception) {
            return new Response(500, ['debug' => $exception->getMessage()]);
        }
    }
    private static function NewButton($button, $input_data, $link)
    {
        if (self::Button_exist($button))  return new Response(200, ['response' => 'Button exist']);
        $unique_id = self::generateRandomString(rand(5, 22));
        $encrypted_button = self::encryptText($button);

        $mainColumns = @$input_data['main'] ?? array();
        $mainColumns = self::encryptText(json_encode($mainColumns));

        $passwordColumns = @$input_data['password'] ?? array();
        $passwordColumns = self::encryptText(json_encode($passwordColumns));

        $all_columns = @$input_data['columns'] ?? array();
        $all_columns = self::encryptText(json_encode($all_columns));

        $columns = (isset($input_data['columns']) && is_array($input_data['columns'])) ? $input_data['columns'] : 'empty';
        $link = self::connectToDB();

        do {
            $unique_id = self::generateRandomString(rand(5, 22));
            $uniqueChekcer = @$link->query("SELECT `id` FROM `buttons` WHERE `unique_id` = '" . $unique_id . "'")->fetch_array()['id'];
            $tableChecker = $link->query("SHOW TABLES LIKE '" . $unique_id . "'");
        } while (!empty($uniqueChekcer) || $tableChecker->fetch_row() > 1);

        $link->query("INSERT INTO `buttons` (`button`,`unique_id`,`main`,`password`,`columns`) VALUES ('" . $encrypted_button . "','" . $unique_id . "','" . $mainColumns . "','" . $passwordColumns . "','" . $all_columns . "')");


        $link->query("create table `$unique_id` ( id INT AUTO_INCREMENT primary key NOT NULL )");
        if ($columns != 'empty')
            foreach ($columns as $column) $link->query("ALTER TABLE `" . $unique_id . "` ADD `" . self::encryptText($column) . "` TEXT(99999) NOT NULL;");

        return new Response(200, ['response' => 'successful']);
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
        $New_button = $data['new'] ?? null;
        $old_button = $data['button'] ?? null;
        if (!$New_button) return new Response(400, ['debug' => 'new name not set']);
        if (self::Button_exist($New_button)) return new Response(200, ['response' => 'new button exist']);
        $encrypted_button = self::encryptText($New_button);
        $button = self::encryptText($old_button);
        $link->query("UPDATE `buttons` SET `button` = '" . $encrypted_button . "' WHERE `buttons`.`button` = '" . $button . "'");
        return new Response(200, ['response' => 'ok']);
    }
    private static function GetButton($button, $link)
    {
        $encrypted_button = self::encryptText($button);
        $buttonData = $link->query("SELECT `unique_id`,`main`,`password`,`columns` FROM `buttons` WHERE `button` = '" . $encrypted_button . "'")->fetch_assoc();
        $uniqueid = $buttonData['unique_id'];
        $main = json_decode(self::decryptText($buttonData['main']), true) ?? null;
        $passwords = json_decode(self::decryptText($buttonData['password'])) ?? null;
        $columns = json_decode(self::decryptText($buttonData['columns']), true);

        if (empty($columns))
            return new Response(200, ['response' => 'no columns']);

        $result  = $link->query("SELECT * FROM  `" . $uniqueid . "`");
        if ($result->num_rows < 1)
            return new Response(200, ['response' => 'no data']);

        $finalData = array();
        while ($cycle = $result->fetch_assoc()) {
            $id = $cycle['id'];
            $cycleArray['id'] = $id;
            if ($main) {
                $mainArray = [];
                $Sqlmain = implode(",", array_map(fn($column) => "`" . self::encryptText($column) . "`", $main));
                $Main_Data = $link->query("SELECT  $Sqlmain FROM `" . $uniqueid . "` WHERE `id` = '" . $id . "'")->fetch_array(MYSQLI_ASSOC);
                foreach ($Main_Data as $key => $value) $mainArray[self::decryptText($key)] = !empty($value) ? self::decryptText($value) : 'empty';

                $cycleArray['main'] = $mainArray;
            } else $cycleArray['main'] = 'empty';
            if ($passwords) {
                $password_array = [];
                $PasswordsSql = implode(",", array_map(fn($column) => "`" . self::encryptText($column) . "`", $passwords));
                $password_Data = $link->query("SELECT  $PasswordsSql FROM `" . $uniqueid . "` WHERE `id` = '" . $id . "'")->fetch_array(MYSQLI_ASSOC);
                foreach ($password_Data as $key => $value) $password_array[self::decryptText($key)] = !empty($value) ? self::decryptText($value) : 'empty';
                $cycleArray['passwords'] = $password_array;
            } else $cycleArray['passwords'] = 'empty';
            array_push($finalData, $cycleArray);
        }
        return new Response(200, ['data' => $finalData]);
    }
    private static function ButtonColumns($button, $link)
    {
        $button = self::encryptText($button);
        $buttonData = @$link->query("SELECT `columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_array(MYSQLI_ASSOC);
        $columns = json_decode(self::decryptText($buttonData['columns']), true) ?? null;
        if (count($columns) > 0) {
            return new Response(200, ['columns' => $columns]);
        } else return new Response(200, ['columns' => 'no columns']);
    }
    private static function insertData($data, $button, $link)
    {
        $info = @$data['info'] ?? null;
        if (!$info) return new Response(400, ['debug' => 'empty info']);

        $button = self::encryptText($button);
        $buttonData = $link->query("SELECT `unique_id`,`columns` FROM `buttons` WHERE `button` = '" . $button . "'")->fetch_assoc();
        $uniqueid = $buttonData['unique_id'];
        $columns = json_decode(self::decryptText($buttonData['columns']), true);
        if (array_keys($info) != $columns) return new Response(400, ['debug' => "Columns don't match"]);
        $encrypted_data = array();
        foreach ($info as $key => $value) $encrypted_data[self::encryptText($key)] = $value ? self::encryptText($value) : 'empty';
        $link->query("INSERT INTO `$uniqueid` (`" . implode("`,`", array_keys($encrypted_data)) . "`) VALUES ('" . implode("','", $encrypted_data) . "')");
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
}