<?php

require 'tools.php';




class Test
{
    use Tools;
    function __construct()
    {
        $key = hash('sha256', '1', true);
        $iraq = 'gn7aMPOahAROZLRIljWbdw==';
        echo self::encryptText("hello from php encryptText", $key);
        echo "\n";
        echo self::decryptText($iraq, $key);
    }
}
new Test();
