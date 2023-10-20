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

namespace ILIAS\MetaData\Services\Paths;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\BuilderInterface as InternalBuilderInterface;

class Builder implements BuilderInterface
{
    protected InternalBuilderInterface $internal_builder;

    public function __construct(InternalBuilderInterface $internal_builder)
    {
        $internal_builder->withRelative(false)
                         ->withLeadsToExactlyOneElement(false);
        $this->internal_builder = $internal_builder;
    }

    public function withNextStep(string $name): BuilderInterface
    {
        $clone = clone $this;
        $clone->internal_builder = $clone->internal_builder->withNextStep($name);
        return $clone;
    }

    public function withNextStepToSuperElement(): BuilderInterface
    {
        $clone = clone $this;
        $clone->internal_builder = $clone->internal_builder->withNextStepToSuperElement();
        return $clone;
    }

    public function withAdditionalFilterAtCurrentStep(
        FilterType $type,
        string ...$values
    ): BuilderInterface {
        $clone = clone $this;
        $clone->internal_builder = $clone->internal_builder->withAdditionalFilterAtCurrentStep(
            $type,
            ...$values
        );
        return $clone;
    }

    public function get(): PathInterface
    {
        return $this->internal_builder->get();
    }
}
