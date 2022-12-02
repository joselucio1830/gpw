<?php

namespace Core\Utils;

class Json
{
    /**
     * @param $string
     * @return bool
     */
    public static function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }
}