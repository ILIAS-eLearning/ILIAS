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
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Refinery\In;

use ILIAS\Refinery\Transformable;

class Series implements Transformable
{
    /** @var Transformable[] */
    private array $transformationStrategies;

    /**
     * @param Transformable[] $transformations
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $transformation) {
            if (!$transformation instanceof Transformable) {
                $transformationClassName = Transformable::class;

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
     * @inheritDoc
     */
    public function transform($from)
    {
        $result = $from;
        foreach ($this->transformationStrategies as $strategy) {
            $result = $strategy->transform($result);
        }

        return $result;
    }
}
