<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Multi byte sensitive string functions
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*/
class ilStr
{
    public static function subStr($a_str, $a_start, $a_length = null)
    {
        if (function_exists("mb_substr")) {
            // bug in PHP < 5.4.12: null is not supported as length (if encoding given)
            // https://github.com/php/php-src/pull/133
            if ($a_length === null) {
                $a_length = mb_strlen($a_str, "UTF-8");
            }
            
            return mb_substr($a_str, $a_start, $a_length, "UTF-8");
        } else {
            return substr($a_str, $a_start, $a_length);
        }
    }

    public static function strPos($a_haystack, $a_needle, $a_offset = null)
    {
        if (function_exists("mb_strpos")) {
            return mb_strpos($a_haystack, $a_needle, $a_offset, "UTF-8");
        } else {
            return strpos($a_haystack, $a_needle, $a_offset);
        }
    }

    public static function strrPos($a_haystack, $a_needle, $a_offset = null)
    {
        if (function_exists("mb_strpos")) {
            return mb_strrpos($a_haystack, $a_needle, $a_offset, "UTF-8");
        } else {
            return strrpos($a_haystack, $a_needle, $a_offset);
        }
    }

    public static function strIPos($a_haystack, $a_needle, $a_offset = null)
    {
        if (function_exists("mb_stripos")) {
            return mb_stripos($a_haystack, $a_needle, $a_offset, "UTF-8");
        } else {
            return stripos($a_haystack, $a_needle, $a_offset);
        }
    }

    /*function strrPos($a_haystack, $a_needle, $a_offset = NULL)
    {
        if (function_exists("mb_strrpos"))
        {
            // only for php version 5.2.0 and above
            if( version_compare(PHP_VERSION, '5.2.0', '>=') )
            {
                return mb_strrpos($a_haystack, $a_needle, $a_offset, "UTF-8");
            }
            else
            {
                @todo: We need an implementation for php versions < 5.2.0
                return mb_strrpos($a_haystack, $a_needle, "UTF-8");
            }
        }
        else
        {
            return strrpos($a_haystack, $a_needle, $a_offset);
        }
    }*/

    public static function strLen($a_string)
    {
        if (function_exists("mb_strlen")) {
            return mb_strlen($a_string, "UTF-8");
        } else {
            return strlen($a_string);
        }
    }

    public static function strToLower($a_string)
    {
        if (function_exists("mb_strtolower")) {
            return mb_strtolower($a_string, "UTF-8");
        } else {
            return strtolower($a_string);
        }
    }

    public static function strToUpper($a_string)
    {
        $a_string = (string) $a_string;
        if (function_exists("mb_strtoupper")) {
            return mb_strtoupper($a_string, "UTF-8");
        } else {
            return strtoupper($a_string);
        }
    }
    
    /**
    * Compare two strings
    */
    public static function strCmp($a, $b)
    {
        global $DIC;

        $ilCollator = null;
        if (isset($DIC["ilCollator"])) {
            $ilCollator = $DIC["ilCollator"];
        }

        if (is_object($ilCollator)) {
            return ($ilCollator->compare(ilStr::strToUpper($a), ilStr::strToUpper($b)) > 0);
        } else {
            return (strcoll(ilStr::strToUpper($a), ilStr::strToUpper($b)) > 0);
        }
    }
    
    /**
     * Shorten text to the given number of bytes.
     * If the character is cutted within a character
     * the invalid character will be shortened, too.
     *
     * E.g: shortenText('€€€',4) will return '€'
     *
     * @param string $a_string
     * @param int $a_start_pos
     * @param int $a_num_bytes
     * @param string $a_encoding [optional]
     * @return string
     */
    public static function shortenText($a_string, $a_start_pos, $a_num_bytes, $a_encoding = 'UTF-8')
    {
        if (function_exists("mb_strcut")) {
            return mb_strcut($a_string, $a_start_pos, $a_num_bytes, $a_encoding);
        }
        return substr($a_string, $a_start_pos, $a_num_bytes);
    }

    /**
    * Check whether string is utf-8
    */
    public static function isUtf8($a_str)
    {
        if (function_exists("mb_detect_encoding")) {
            if (mb_detect_encoding($a_str, "UTF-8", true) == "UTF-8") {
                return true;
            }
        } else {
            // copied from http://www.php.net/manual/en/function.mb-detect-encoding.php
            $c = 0;
            $b = 0;
            $bits = 0;
            $len = strlen($a_str);
            for ($i = 0; $i < $len; $i++) {
                $c = ord($a_str[$i]);
                if ($c > 128) {
                    if (($c >= 254)) {
                        return false;
                    } elseif ($c >= 252) {
                        $bits = 6;
                    } elseif ($c >= 248) {
                        $bits = 5;
                    } elseif ($c >= 240) {
                        $bits = 4;
                    } elseif ($c >= 224) {
                        $bits = 3;
                    } elseif ($c >= 192) {
                        $bits = 2;
                    } else {
                        return false;
                    }
                    if (($i + $bits) > $len) {
                        return false;
                    }
                    while ($bits > 1) {
                        $i++;
                        $b = ord($a_str[$i]);
                        if ($b < 128 || $b > 191) {
                            return false;
                        }
                        $bits--;
                    }
                }
            }
            return true;
        }
        return false;
    }


    /**
     * Get all positions of a string
     *
     * @param string the string to search in
     * @param string the string to search for
     * @return array all occurences of needle in haystack
     */
    public static function strPosAll($a_haystack, $a_needle)
    {
        $positions = array();
        $cpos = 0;
        while (is_int($pos = strpos($a_haystack, $a_needle, $cpos))) {
            $positions[] = $pos;
            $cpos = $pos + 1;
        }
        return $positions;
    }

    /**
     * Replaces the first occurence of $a_old in $a_str with $a_new
     */
    public static function replaceFirsOccurence($a_old, $a_new, $a_str)
    {
        if (is_int(strpos($a_str, $a_old))) {
            $a_str = substr_replace($a_str, $a_new, strpos($a_str, $a_old), strlen($a_old));
        }
        return $a_str;
    }

    /**
     * Convert a value given in camel case conversion to underscore case conversion (e.g. MyClass to my_class)
     * @param string $value Value in lower camel case conversion
     * @return string The value in underscore case conversion
     */
    public static function convertUpperCamelCaseToUnderscoreCase($value)
    {
        return strtolower(preg_replace(
            array('#(?<=(?:[A-Z]))([A-Z]+)([A-Z][A-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'),
            array('\1_\2', '_\1'),
            $value
        ));
    }

    /**
     * Return string as byte array
     * Note: Use this for debugging purposes only. If strlen is overwritten by mb_ functions
     * (PHP config) this will return not all characters
     *
     * @param string $a_str string
     * @return array array of bytes
     */
    public static function getBytesForString($a_str)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($a_str); $i++) {
            $bytes[] = ord($a_str[$i]);
        }
        return $bytes;
    }
    
    /**
     * Normalize UTF8 string
     *
     * @param string $a_str string
     * @return string
     */
    public static function normalizeUtf8String($a_str)
    {
        include_once("./include/Unicode/UtfNormal.php");
        return UtfNormal::toNFC($a_str);
    }
}
