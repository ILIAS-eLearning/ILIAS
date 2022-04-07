<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

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
