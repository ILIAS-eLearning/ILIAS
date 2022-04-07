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

namespace ILIAS\Refinery\In;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use InvalidArgumentException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Parallel implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @var Transformation[]
     */
    private array $transformationStrategies;

    /**
     * @param array $transformations
     * @throws InvalidArgumentException
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $transformation) {
            if (!$transformation instanceof Transformation) {
                $transformationClassName = Transformation::class;

                throw new ConstraintViolationException(
                    sprintf('The array MUST contain only "%s" instances', $transformationClassName),
                    'not_a_transformation',
                    $transformationClassName
                );
            }
        }
        $this->transformationStrategies = $transformations;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        $results = array();
        foreach ($this->transformationStrategies as $strategy) {
            $results[] = $strategy->transform($from);
        }

        return $results;
    }
}
