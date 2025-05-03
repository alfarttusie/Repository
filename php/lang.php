<?php

class lang
{
    private static array $lang = [];

    public static function load(string $langCode = 'ar'): void
    {
        self::$lang =  require __DIR__ . "/lang/$langCode.php";
    }

    public static function get(string $key): string
    {
        return self::$lang[$key] ?? $key;
    }
    public static function getAll(): array
    {
        return self::$lang;
    }
}
