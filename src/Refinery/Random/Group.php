<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
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
    public function shuffleArray(Seed $seed) : Transformation
    {
        return new ShuffleTransformation($seed);
    }

    /**
     * Get a transformation which will return the given value as is.
     * Everything can be supplied to the transformation.
     */
    public function dontShuffle() : Transformation
    {
        return new IdentityTransformation();
    }
}
