<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery\Random\Transformation;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Refinery\IdentityTransformation;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\DeriveApplyToFromTransform;

/**
 * !! BEWARE OF THE SIDE EFFECT. This Transformation is not Side Effect free !!
 * Shuffling and seeding of the random generator is not side effect free and done when transforming a value.
 * This class is an exception to the rule of the normally side effect free transformations.
 * @see https://github.com/ILIAS-eLearning/ILIAS/pull/476/files#diff-a6b45507ea92787f1788b74d31ba62162dd9b09f00e7a9dea804be783c09afecR8
 * and https://github.com/ILIAS-eLearning/ILIAS/pull/1707/files#diff-cbb4c50b8e633da5c3461f5b4bdf0f29c11199213ae2c60788af66b885b6bb5e
 */
class ShuffleTransformation implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    private Seed $seed;

    public function __construct(Seed $seed)
    {
        $this->seed = $seed;
    }

    /**
     * @throws ConstraintViolationException
     * @return array
     */
    public function transform($array)
    {
        if (!is_array($array)) {
            throw new ConstraintViolationException('not an array', 'not_an_array');
        }
        $this->seed->seedRandomGenerator();
        \shuffle($array);

        return $array;
    }
}
