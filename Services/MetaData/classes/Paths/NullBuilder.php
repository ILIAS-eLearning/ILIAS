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

namespace ILIAS\MetaData\Paths;

use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;

class NullBuilder implements BuilderInterface
{
    public function withRelative(bool $is_relative): BuilderInterface
    {
        return new NullBuilder();
    }

    public function withLeadsToExactlyOneElement(bool $leads_to_one): BuilderInterface
    {
        return new NullBuilder();
    }

    public function withNextStep(string $name, bool $add_as_first = false): BuilderInterface
    {
        return new NullBuilder();
    }

    public function withNextStepToSuperElement(bool $add_as_first = false): BuilderInterface
    {
        return new NullBuilder();
    }

    public function withAdditionalFilterAtCurrentStep(FilterType $type, string ...$values): BuilderInterface
    {
        return new NullBuilder();
    }

    public function get(): PathInterface
    {
        return new NullPath();
    }

    public function withNextStepFromStep(StepInterface $next_step, bool $add_as_first = false): BuilderInterface
    {
        return new NullBuilder();
    }
}
