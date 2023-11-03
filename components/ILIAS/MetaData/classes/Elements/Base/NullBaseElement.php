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

namespace ILIAS\MetaData\Elements\Base;

use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class NullBaseElement implements BaseElementInterface
{
    public function getMDID(): int|NoID
    {
        return 0;
    }

    public function getDefinition(): DefinitionInterface
    {
        return new NullDefinition();
    }

    public function getSubElements(): \Generator
    {
        yield from [];
    }

    public function getSuperElement(): ?BaseElementInterface
    {
        return null;
    }

    public function isRoot(): bool
    {
        return false;
    }
}
