<?php

declare(strict_types=1);

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

namespace ILIAS\Refinery\In;

use ILIAS\Refinery\Transformation;

class Group
{
    /**
     * Takes an array of transformations and performs them one after
     * another on the result of the previous transformation
     * @param Transformation[] $inTransformations
     * @return Transformation
     */
    public function series(array $inTransformations): Transformation
    {
        return new Series($inTransformations);
    }

    /**
     * Takes an array of transformations and performs each on the
     * input value to form a tuple of the results
     * @param Transformation[] $inTransformations
     * @return Transformation
     */
    public function parallel(array $inTransformations): Transformation
    {
        return new Parallel($inTransformations);
    }
}
