<?php declare(strict_types=1);

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

namespace ILIAS\Refinery\Random\Transformation;

use ILIAS\Refinery\Random\Seed\Seed;
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

    public function transform($from) : array
    {
        if (!is_array($from)) {
            throw new ConstraintViolationException('not an array', 'no_array');
        }
        $this->seed->seedRandomGenerator();
        shuffle($from);

        return $from;
    }
}
