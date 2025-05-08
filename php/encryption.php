<?php

trait encryption
{
    private static $CIPHERING = 'AES-256-CBC';
    private static $OPTIONS = OPENSSL_RAW_DATA;
    private static $encryptionKey;
    private static $IV_KEY = "99754f94d3106633";


    
    public static function encryptText(string $plaintext, $Key = null): string
    {
        $encryptionKey = $Key ?? self::$encryptionKey;
        $encrypted = openssl_encrypt($plaintext, self::$CIPHERING, $encryptionKey, self::$OPTIONS, self::$IV_KEY);
        return base64_encode($encrypted);
    }

    public static function decryptText(string $encryptedText,  $Key = null): string
    {
        $encryptionKey = $Key ?? self::$encryptionKey;
        $encryptedText = base64_decode($encryptedText);
        return openssl_decrypt($encryptedText, self::$CIPHERING, $encryptionKey, self::$OPTIONS, self::$IV_KEY);
    }
}