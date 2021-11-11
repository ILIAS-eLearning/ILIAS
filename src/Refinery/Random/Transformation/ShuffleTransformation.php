<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery\Random\Transformation;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\Random\Seed\Seed;

/**
 * !! BEWARE OF THE SIDE EFFECT. This Transformation is not Side Effect free !!
 * Shuffling and seeding of the random generator is not side effect free and done when transforming a value.
 * This class is an expection to the rule of the normally side effect free transformations.
 */
class ShuffleTransformation extends IdentityTransformation
{
    private Seed $seed;

    public function __construct(Seed $seed)
    {
        $this->seed = $seed;
    }

    /**
     * @return Result<array>
     */
    protected function validate($from) : Result
    {
        return is_array($from) ? new Ok($from) : new Error('I need an array');
    }

    /**
     * @param array $array
     * @return array
     */
    protected function saveTransform($array)
    {
        $this->seed->seedRandomGenerator();
        \shuffle($array);

        return $array;
    }
}
