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

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ReflectionClass;
use ReflectionException;

class NewObjectTransformation implements Transformation
{
    use DeriveApplyToFromTransform;

    private string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function transform($from)
    {
        $class = new ReflectionClass($this->className);
        $instance = $class->newInstanceArgs($from);

        return $instance;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
