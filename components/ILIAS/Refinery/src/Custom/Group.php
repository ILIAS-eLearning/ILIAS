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

namespace ILIAS\Refinery\Custom;

use ILIAS\Refinery\Constraint as ConstraintInterface;
use ILIAS\Refinery\Transformation as TransformationInterface;
use ILIAS\Refinery\BuildTransformation;

class Group
{
    public function __construct(private readonly BuildTransformation $build_transformation)
    {
    }

    /**
     * @param callable $callable
     * @param string|callable $error
     */
    public function constraint(callable $callable, $error): ConstraintInterface
    {
        return $this->build_transformation->fromConstraint(new Constraint(
            $callable
        ))->withProblemBuilder(is_callable($error) ? $error : fn() => $error);
    }

    public function transformation(callable $transform): TransformationInterface
    {
        return $this->build_transformation->fromTransformable(new Transformation($transform));
    }
}
