<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\In;

use ILIAS\Refinery\Transformation;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Group
{

    /**
     * Takes an array of transformations and performs them one after
     * another on the result of the previous transformation
     */
    public function series(array $inTransformations) : Transformation
    {
        return new Series($inTransformations);
    }

    /**
     * Takes an array of transformations and performs each on the
     * input value to form a tuple of the results
     */
    public function parallel(array $inTransformations) : Transformation
    {
        return new Parallel($inTransformations);
    }
}
