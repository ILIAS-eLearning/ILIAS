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

namespace ILIAS\Refinery\Random;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Refinery\IdentityTransformation;

class Group
{
    /**
     * Get a transformation which will shuffle a given array.
     * Only arrays can be supplied to the transformation.
     *
     * The transformation will be shuffled with the given $seed.
     *
     * !! BEWARE OF THE SIDE EFFECT. This Transformation is not Side Effect free !!
     * The internal state of the PRNG will be advanced on every usage.
     */
    public function shuffleArray(Seed $seed): Transformation
    {
        return new ShuffleTransformation($seed);
    }

    /**
     * Get a transformation which will return the given value as is.
     * Everything can be supplied to the transformation.
     */
    public function dontShuffle(): Transformation
    {
        return new IdentityTransformation();
    }
}
