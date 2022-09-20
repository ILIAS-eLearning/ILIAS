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
 * ilArrayUtil class
 * various functions, usage as namespace
 *
 * @author     Sascha Hofmann <saschahofmann@gmx.de>
 * @author     Alex Killing <alex.killing@gmx.de>
 *
 * @deprecated The 2021 Technical Board has decided to mark the ilUtil class as deprecated. The ilUtil is a historically
 * grown helper class with many different UseCases and functions. The class is not under direct maintainership and the
 * responsibilities are unclear. In this context, the class should no longer be used in the code and existing uses
 * should be converted to their own service in the medium term. If you need ilUtil for the implementation of a new
 * function in ILIAS > 7, please contact the Technical Board.
 */
class ilArrayUtil
{
    /**
     * Quotes all members of an array for usage in DB query statement.
     *
     * @deprecated
     */
    public static function quoteArray(array $a_array): array
    {
        global $DIC;

        $ilDB = $DIC->database();


        if (!is_array($a_array) or !count($a_array)) {
            return ["''"];
        }

        foreach ($a_array as $k => $item) {
            $a_array[$k] = $ilDB->quote($item);
        }

        return $a_array;
    }

    /**
     * @param $data string|array
     * @deprecated
     */
    public static function stripSlashesRecursive($a_data, bool $a_strip_html = true, string $a_allow = ""): array
    {
        if (is_array($a_data)) {
            foreach ($a_data as $k => $v) {
                if (is_array($v)) {
                    $a_data[$k] = ilArrayUtil::stripSlashesRecursive($v, $a_strip_html, $a_allow);
                } else {
                    $a_data[$k] = ilUtil::stripSlashes($v, $a_strip_html, $a_allow);
                }
            }
        } else {
            $a_data = ilUtil::stripSlashes($a_data, $a_strip_html, $a_allow);
        }

        return $a_data;
    }

    /**
     * @deprecated
     */
    public static function stripSlashesArray(array $a_arr, bool $a_strip_html = true, string $a_allow = ""): array
    {
        foreach ($a_arr as $k => $v) {
            $a_arr[$k] = ilUtil::stripSlashes($v, $a_strip_html, $a_allow);
        }

        return $a_arr;
    }

    /**
     * @deprecated
     */
    public static function sortArray(
        array $array,
        string $a_array_sortby_key,
        string $a_array_sortorder = "asc",
        bool $a_numeric = false,
        bool $a_keep_keys = false
    ): array {
        if (!$a_keep_keys) {
            return self::stableSortArray($array, $a_array_sortby_key, $a_array_sortorder, $a_numeric);
        }

        global $array_sortby, $array_sortorder;
        $array_sortby = $a_array_sortby_key;

        if ($a_array_sortorder == "desc") {
            $array_sortorder = "desc";
        } else {
            $array_sortorder = "asc";
        }
        if ($a_numeric) {
            if ($a_keep_keys) {
                uasort($array, [ilArrayUtil::class, "sort_func_numeric"]);
            } else {
                usort($array, [ilArrayUtil::class, "sort_func_numeric"]);
            }
        } else {
            if ($a_keep_keys) {
                uasort($array, [ilArrayUtil::class, "sort_func"]);
            } else {
                usort($array, [ilArrayUtil::class, "sort_func"]);
            }
        }

        return $array;
    }

    /**
     * @deprecated
     */
    private static function sort_func(array $left, array $right): int
    {
        global $array_sortby, $array_sortorder;

        if (!isset($array_sortby)) {
            // occurred in: setup -> new client -> install languages -> sorting of languages
            $array_sortby = 0;
        }

        $leftValue = (string) ($left[$array_sortby] ?? '');
        $rightValue = (string) ($right[$array_sortby] ?? '');

        // this comparison should give optimal results if
        // locale is provided and mb string functions are supported
        if ($array_sortorder === "asc") {
            return ilStr::strCmp($leftValue, $rightValue);
        } elseif ($array_sortorder === "desc") {
            return ilStr::strCmp($rightValue, $leftValue);
        }

        return 0;
    }

    /**
     * @deprecated
     */
    private static function sort_func_numeric(array $left, array $right): int
    {
        global $array_sortby, $array_sortorder;

        $leftValue = (string) ($left[$array_sortby] ?? '');
        $rightValue = (string) ($right[$array_sortby] ?? '');

        if ($array_sortorder === "asc") {
            return $leftValue <=> $rightValue;
        } elseif ($array_sortorder === "desc") {
            return $rightValue <=> $leftValue;
        }

        return 0;
    }

    /**
     * @param array    $array
     * @param callable $cmp_function
     * @return void
     */
    private static function mergesort(array &$array, callable $cmp_function = null): void
    {
        if ($cmp_function === null) {
            $cmp_function = 'strcmp';
        }
        // Arrays of size < 2 require no action.
        if (count($array) < 2) {
            return;
        }

        // Split the array in half
        $halfway = count($array) / 2;
        $array1 = array_slice($array, 0, $halfway);
        $array2 = array_slice($array, $halfway);

        // Recurse to sort the two halves
        ilArrayUtil::mergesort($array1, $cmp_function);
        ilArrayUtil::mergesort($array2, $cmp_function);

        // If all of $array1 is <= all of $array2, just append them.
        if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
            $array = array_merge($array1, $array2);
            return;
        }

        // Merge the two sorted arrays into a single sorted array
        $array = [];
        $ptr1 = $ptr2 = 0;
        while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
            if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
                $array[] = $array1[$ptr1++];
            } else {
                $array[] = $array2[$ptr2++];
            }
        }

        // Merge the remainder
        while ($ptr1 < count($array1)) {
            $array[] = $array1[$ptr1++];
        }
        while ($ptr2 < count($array2)) {
            $array[] = $array2[$ptr2++];
        }
    }

    /**
     * Sort an aray using a stable sort algorithm, which preveserves the sequence
     * of array elements which have the same sort value.
     * To sort an array by multiple sort keys, invoke this function for each sort key.
     *
     * @deprecated
     */
    public static function stableSortArray(
        array $array,
        string $a_array_sortby,
        string $a_array_sortorder = "asc",
        bool $a_numeric = false
    ): array {
        global $array_sortby, $array_sortorder;

        $array_sortby = $a_array_sortby;

        if ($a_array_sortorder == "desc") {
            $array_sortorder = "desc";
        } else {
            $array_sortorder = "asc";
        }

        // Create a copy of the array values for sorting
        $sort_array = array_values($array);

        if ($a_numeric) {
            ilArrayUtil::mergesort($sort_array, [ilArrayUtil::class, "sort_func_numeric"]);
        } else {
            ilArrayUtil::mergesort($sort_array, [ilArrayUtil::class, "sort_func"]);
        }

        return $sort_array;
    }
}
