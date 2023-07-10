<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/


class ilCSVUtil
{
    /**
     * Convertes an array for CSV usage
     *
     * Processes an array as a CSV row and converts the array values to correct CSV
     * values. The "converted" array is returned
     *
     * @param array  $row       The array containing the values for a CSV row
     * @param bool   $quoteAll  Indicates to quote every value (=TRUE) or only values containing quotes and separators (=FALSE, default)
     * @param string $separator The value separator in the CSV row (used for quoting) (; = default)
     * @return array The converted array ready for CSV use
     * @deprecated
     */
    public static function &processCSVRow(
        array &$row,
        bool $quoteAll = false,
        string $separator = ";",
        bool $outUTF8 = false,
        bool $compatibleWithMSExcel = true
    ): array {
        $resultarray = [];
        foreach ($row as $rowindex => $entry) {
            $surround = false;
            if ($quoteAll) {
                $surround = true;
            }
            if (strpos($entry, "\"") !== false) {
                $entry = str_replace("\"", "\"\"", $entry);
                $surround = true;
            }
            if (strpos($entry, $separator) !== false) {
                $surround = true;
            }
            if ($compatibleWithMSExcel) {
                // replace all CR LF with LF (for Excel for Windows compatibility
                $entry = str_replace(chr(13) . chr(10), chr(10), $entry);
            }
            if ($surround) {
                if ($outUTF8) {
                    $resultarray[$rowindex] = "\"" . $entry . "\"";
                } else {
                    $resultarray[$rowindex] = utf8_decode("\"" . $entry . "\"");
                }
            } else {
                if ($outUTF8) {
                    $resultarray[$rowindex] = $entry;
                } else {
                    $resultarray[$rowindex] = utf8_decode($entry);
                }
            }
        }
        return $resultarray;
    }
}
