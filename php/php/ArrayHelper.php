<?php


trait ArrayHelper
{

    private static function Additem($array, $item)
    {
        array_push($array, $item);
        $array = array_values($array);
        return $array;
    }
    private static function replace_item(array $array, string $item, string $new)
    {
        $itemkey = array_search($item, $array);
        unset($array[$itemkey]);
        array_push($array, $new);
        return array_values($array);
    }
}