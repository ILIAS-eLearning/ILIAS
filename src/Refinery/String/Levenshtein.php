<?php
declare(strict_types=1);

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;

class Levenshtein implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /** @var string  */
    private string $mainStr;
    /** @var int  */
    private int $maximumDistance;
    /** @var float  */
    private float $costIns;
    /** @var float  */
    private float $costRep;
    /** @var float  */
    private float $costDel;

    /**
     * This constructor allows to parameterize the levenshtein distance function.
     * So the standard costs for insert, delete, replacement can be adjusted.
     * Due to the definition of the interface, the primary string that should be compared several times
     * also is to be handed over here.
     *
     * @param string $str string for distance calculation
     * @param int $maximumDistance maximum allowed distance, limits the calculation of the Levenshtein distance. A maximum distance of 0 disables the function
     * @param float $costIns cost for insertion default 1.0
     * @param float $costRep cost for replacement default 1.0
     * @param float $costDel cost for deletion default 1.0
     */
    public function __construct(string $str = "", int $maximumDistance = 0,
                                float $costIns = 1.0, float $costRep = 1.0, float $costDel = 1.0)
    {
        $this->mainStr= $str;
        $this->maximumDistance = $maximumDistance;
        $this->costIns = $costIns;
        $this->costRep = $costRep;
        $this->costDel = $costDel;
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
     * @param string $str string which is used for a repeated distance calculation
     * @return float with Levenshtein distance, if an interrupt happens earlier than the return value is a -1
     */
    protected function levenshtein(string $str): float
    {
        $matrix = [];
        $str1Array = $this->StringToArray($this->mainStr);
        $str2Array = $this->StringToArray($str);
        $str1Length = count($str1Array);
        $str2Length = count($str2Array);

        // if the difference between string length is bigger than the maximum allowed levenshtein distance
        // the code can be skipped
        if (abs($str1Length - $str2Length) > $this->maximumDistance && $this->maximumDistance != 0){
            return -1;
        }

        $row = [];
        $row[0] = 0.0;
        for ($j = 1; $j < $str2Length + 1; $j++) {
            $row[$j] = $j * $this->costIns;
        }

        $matrix[0] = $row;
        for ($i = 0; $i < $str1Length; $i++) {
            $row = [];
            $row[0] = ($i + 1) * $this->costDel;
            for ($j = 0; $j < $str2Length; $j++) {
                $row[$j + 1] = min(
                    $matrix[$i][$j + 1] + $this->costDel,
                    $row[$j] + $this->costIns,
                    $matrix[$i][$j] + ($str1Array[$i] === $str2Array[$j] ? 0.0 : $this->costRep)
                );
            }
            // maximum distance reached
            if (min($row) > $this->maximumDistance && $this->maximumDistance != 0){
                return -1;
            }
            $matrix[$i + 1] = $row;
        }
        return $matrix[$str1Length][$str2Length];
    }

    /**
     * Helper function for levenshtein distance calculation, used to convert strings into arrays.
     *
     * @param string $str the string that is converted into an array
     * @return array an array containing the characters of the string, each in a single cell
     */
    private function StringToArray(string $str): array
    {
        $length = strlen($str);
        $array = [];
        for ($i = 0; $i < $length; $i++) {
            $array[$i] = substr($str, $i, 1);
        }
        return $array;
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