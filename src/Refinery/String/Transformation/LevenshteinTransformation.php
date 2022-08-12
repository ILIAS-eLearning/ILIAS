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
 * If this is not the case, or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Refinery\String\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;
use ilStr;

class LevenshteinTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /** @var string  */
    private $primary_string;
    /** @var int  */
    private $maximum_distance;
    /** @var float  */
    private $cost_insertion;
    /** @var float  */
    private $cost_replacement;
    /** @var float  */
    private $cost_deletion;

    /**
     * This constructor allows to parameterize the levenshtein distance function.
     * So the standard costs for insert, delete, replacement can be adjusted.
     * Due to the definition of the interface, the primary string that should be compared several times
     * also is to be handed over here.
     *
     * @param string $primary_string string for distance calculation, should be the one string that doesn't change.
     * @param int $maximum_distance maximum allowed distance, limits the calculation of the Levenshtein distance. A maximum distance of 0 disables the function
     * @param float $cost_insertion custom cost for insertion, default 1.0
     * @param float $cost_replacement custom cost for replacement, default 1.0
     * @param float $cost_deletion custom cost for deletion, default 1.0
     */
    public function __construct(
        string $primary_string = "",
        int    $maximum_distance = 0,
        float  $cost_insertion = 1.0,
        float  $cost_replacement = 1.0,
        float  $cost_deletion = 1.0
    ) {
        $this->primary_string= $primary_string;
        $this->maximum_distance = $maximum_distance;
        $this->cost_insertion = $cost_insertion;
        $this->cost_replacement = $cost_replacement;
        $this->cost_deletion = $cost_deletion;
    }

    /**
     * Levenshtein function alternative code as mentioned in the bug report:
     * https://mantis.ilias.de/view.php?id=17861
     * Original code under MIT-License:
     * https://github.com/GordonLesti/levenshtein/blob/master/src/Levenshtein.php
     *
     * Runtime improvements have been added:
     * A interruption that triggers when the difference in length is bigger than the allowed maximum distance
     * A maximum distance that interrupts the execution of the algorithm, if it already is worse than the allowed
     *
     * @param string $secondary_string string which is used for a repeated distance calculation
     * @return float with Levenshtein distance, if an interrupt happens earlier than the return value is a -1
     */
    protected function levenshtein(string $secondary_string): float
    {
        $cost_matrix = [];
        $primary_string_array = $this->stringToCharacterArray($this->primary_string);
        $secondary_string_array = $this->stringToCharacterArray($secondary_string);
        $primary_string_length = count($primary_string_array);
        $secondary_string_length = count($secondary_string_array);

        // if the difference between string length is bigger than the maximum allowed levenshtein distance
        // the code can be skipped
        if (abs($primary_string_length - $secondary_string_length) > $this->maximum_distance && $this->maximum_distance != 0) {
            return -1.0;
        }

        $current_row = [];
        $current_row[0] = 0.0;
        for ($j = 1; $j < $secondary_string_length + 1; $j++) {
            $current_row[$j] = $j * $this->cost_insertion;
        }

        $cost_matrix[0] = $current_row;
        for ($i = 0; $i < $primary_string_length; $i++) {
            $current_row = [];
            $current_row[0] = ($i + 1) * $this->cost_deletion;
            for ($j = 0; $j < $secondary_string_length; $j++) {
                $current_row[$j + 1] = min(
                    $cost_matrix[$i][$j + 1] + $this->cost_deletion,
                    $current_row[$j] + $this->cost_insertion,
                    $cost_matrix[$i][$j] + ($primary_string_array[$i] === $secondary_string_array[$j] ? 0.0 : $this->cost_replacement)
                );
            }
            // maximum distance reached
            if (min($current_row) > $this->maximum_distance && $this->maximum_distance != 0) {
                return -1.0;
            }
            $cost_matrix[$i + 1] = $current_row;
        }
        return $cost_matrix[$primary_string_length][$secondary_string_length];
    }

    /**
     * Helper function for levenshtein distance calculation, used to convert strings into character arrays.
     *
     * @param string $string_to_convert the string that is converted into an character array
     * @return array an array containing the characters of the string, each in a single cell
     */
    private function stringToCharacterArray(string $string_to_convert): array
    {
        $length = ilStr::strLen($string_to_convert);
        $character_array = [];
        for ($index = 0; $index < $length; $index++) {
            $character_array[$index] = ilStr::subStr($string_to_convert, $index, 1);
        }
        return $character_array;
    }

    /**
     * The transform method checks if the $form variable contains a string
     * alternatively an InvalidArgumentException is thrown.
     * After that the Levenshtein method is executed with the given string.
     *
     * @thorws \InvalidArgumentException
     * @param string $from a string is excepted with the word used to calculate the Levenshtein distance.
     * @return float with Levenshtein distance, if an interrupt happens earlier than the return value is a -1.
     */
    public function transform($from): float
    {
        // check if $from is string otherwise exception
        if (!is_string($from)) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        // call levenshtein methode return result
        return $this->levenshtein($from);
    }
}
