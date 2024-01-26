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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\String\Transformation\LevenshteinTransformation;

class Levenshtein
{
    /**
     * Creates an object of the Levenshtein class
     * This class calculates the levenshtein distance with a default value of 1.0 per insert, delete, replacement.
     *
     * @param string $str string for distance calculation
     * @param int $maximumDistance maximum allowed distance, limits the calculation of the Levenshtein distance. A maximum distance of 0 disables the function
     * @return Transformation
     */
    public function standard(string $str, int $maximumDistance): Transformation
    {
        return new LevenshteinTransformation($str, $maximumDistance, 1.0, 1.0, 1.0);
    }

    /**
     * Creates an object of the Levenshtein class
     * This class calculates the levenshtein distance with custom parameters for insert, delete, replacement.
     *
     * @param string $str string for distance calculation
     * @param int $maximum_distance maximum allowed distance, limits the calculation of the Levenshtein distance. A maximum distance of 0 disables the function
     * @param float $cost_insertion cost for insertion default 1.0
     * @param float $cost_replacement cost for replacement default 1.0
     * @param float $cost_deletion cost for deletion default 1.0
     * @return Transformation
     */
    public function custom(
        string $str,
        int $maximum_distance,
        float $cost_insertion,
        float $cost_replacement,
        float $cost_deletion
    ): Transformation {
        return new LevenshteinTransformation($str, $maximum_distance, $cost_insertion, $cost_replacement, $cost_deletion);
    }
}
