<?php



trait encryption

{

    private static $CIPHERING = 'AES-256-CBC';

    private static $OPTIONS = OPENSSL_RAW_DATA;
    private static $encryptionKey;


    public static function encryptText(string $plaintext, $Key = null, $IV_KEY = null): string
    {
        $encryptionKey = $Key ?? self::$encryptionKey;
        $encryptionKey = hash('sha256', $encryptionKey, true);
        $IV_KEY = $IV_KEY ?? self::$IV_KEY;
        $encrypted = openssl_encrypt($plaintext, self::$CIPHERING, $encryptionKey, self::$OPTIONS,  $IV_KEY);
        return base64_encode($encrypted);
    }

    public static function decryptText(string $encryptedText,  $Key = null, $IV_KEY = null): string
    {
        $encryptionKey = $Key ?? self::$encryptionKey;
        $encryptionKey = hash('sha256', $encryptionKey, true);
        $IV_KEY = $IV_KEY ?? self::$IV_KEY;
        $encryptedText = base64_decode($encryptedText);
        return openssl_decrypt($encryptedText, self::$CIPHERING, $encryptionKey, self::$OPTIONS,  $IV_KEY);
    }
}