<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @deprecated
 */
class ilStr
{
    public static function subStr(string $a_str, int $a_start, ?int $a_length = null): string
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

    /**
     * @return false|int|true
     */
    public static function strPos(string $a_haystack, string $a_needle, ?int $a_offset = null)
    {
        if (function_exists("mb_strpos")) {
            return mb_strpos($a_haystack, $a_needle, $a_offset, "UTF-8");
        } else {
            return strpos($a_haystack, $a_needle, $a_offset);
        }
    }

    /**
     * @return false|int
     */
    public static function strIPos(string $a_haystack, string $a_needle, ?int $a_offset = null)
    {
        if (function_exists("mb_stripos")) {
            return mb_stripos($a_haystack, $a_needle, $a_offset ?? 0, "UTF-8");
        } else {
            return stripos($a_haystack, $a_needle, $a_offset);
        }
    }

    public static function strLen(string $a_string): int
    {
        if (function_exists("mb_strlen")) {
            return mb_strlen($a_string, "UTF-8");
        } else {
            return strlen($a_string);
        }
    }

    public static function strToLower(string $a_string): string
    {
        if (function_exists("mb_strtolower")) {
            return mb_strtolower($a_string, "UTF-8");
        } else {
            return strtolower($a_string);
        }
    }

    public static function strToUpper(string $a_string): string
    {
        if (function_exists("mb_strtoupper")) {
            return mb_strtoupper($a_string, "UTF-8");
        } else {
            return strtoupper($a_string);
        }
    }

    public static function strCmp(string $a, string $b): int
    {
        return strcoll(ilStr::strToUpper($a), ilStr::strToUpper($b));
    }

    /**
     * Shorten text to the given number of bytes.
     * If the character is cut within a character
     * the invalid character will be shortened, too.
     *
     * E.g: shortenText('€€€',4) will return '€'
     */
    public static function shortenText(
        string $a_string,
        int $a_start_pos,
        int $a_num_bytes,
        string $a_encoding = 'UTF-8'
    ): string {
        if (function_exists("mb_strcut")) {
            return mb_strcut($a_string, $a_start_pos, $a_num_bytes, $a_encoding);
        }
        return substr($a_string, $a_start_pos, $a_num_bytes);
    }

    /**
     * Check whether string is utf-8
     */
    public static function isUtf8(string $a_str): bool
    {
        if (function_exists("mb_detect_encoding")) {
            if (mb_detect_encoding($a_str, "UTF-8", true) === "UTF-8") {
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
     * Convert a value given in camel case conversion to underscore case conversion (e.g. MyClass to my_class)
     *
     * @param string $value Value in lower camel case conversion
     * @return string The value in underscore case conversion
     */
    public static function convertUpperCamelCaseToUnderscoreCase(string $value): string
    {
        return strtolower(
            preg_replace(
                ['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][A-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'],
                ['\1_\2', '_\1'],
                $value
            )
        );
    }

    /**
     * @deprecated
     */
    public static function shortenTextExtended(
        string $a_str,
        int $a_len,
        bool $a_dots = false,
        bool $a_next_blank = false,
        bool $a_keep_extension = false
    ): string {
        if (ilStr::strLen($a_str) > $a_len) {
            /*
             * When adding dots, the dots have to be included in the length such
             * that the total length of the resulting string does not exceed
             * the given maximum length (see BT 33865).
             */
            if ($a_dots) {
                $a_len--;
            }
            if ($a_next_blank) {
                $len = ilStr::strPos($a_str, " ", $a_len);
            } else {
                $len = $a_len;
            }
            // BEGIN WebDAV
            //             - Shorten names in the middle, before the filename extension
            //             Workaround for Windows WebDAV Client:
            //             Use the unicode ellipsis symbol for shortening instead of
            //             three full stop characters.
            $p = false;
            if ($a_keep_extension) {
                $p = strrpos($a_str, '.');    // this messes up normal shortening, see bug #6190
            }
            if ($p === false || $p == 0 || strlen($a_str) - $p > $a_len) {
                $a_str = ilStr::subStr($a_str, 0, $len);
                if ($a_dots) {
                    $a_str .= "\xe2\x80\xa6"; // UTF-8 encoding for Unicode ellipsis character.
                }
            } else {
                if ($a_dots) {
                    $a_str = ilStr::subStr($a_str, 0, $len - (strlen($a_str) - $p + 1)) . "\xe2\x80\xa6" . substr(
                        $a_str,
                        $p
                    );
                } else {
                    $a_str = ilStr::subStr($a_str, 0, $len - (strlen($a_str) - $p + 1)) . substr($a_str, $p);
                }
            }
        }

        return $a_str;
    }

    /**
     * Ensure that the maximum word lenght within a text is not longer
     * than $a_len
     *
     * @depends
     */
    public static function shortenWords(string $a_str, int $a_len = 30, bool $a_dots = true): string
    {
        $str_arr = explode(" ", $a_str);

        for ($i = 0; $i < count($str_arr); $i++) {
            if (ilStr::strLen($str_arr[$i]) > $a_len) {
                $str_arr[$i] = ilStr::subStr($str_arr[$i], 0, $a_len);
                if ($a_dots) {
                    $str_arr[$i] .= "...";
                }
            }
        }

        return implode(" ", $str_arr);
    }
}
