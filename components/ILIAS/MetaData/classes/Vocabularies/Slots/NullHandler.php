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

namespace ILIAS\MetaData\Vocabularies\Slots;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\ConditionInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Elements\Data\Type as DataType;
use ILIAS\MetaData\Elements\Data\Type;

class NullHandler implements HandlerInterface
{
    public function pathForSlot(Identifier $identifier): PathInterface
    {
        return new NullPath();
    }

    public function isSlotConditional(Identifier $identifier): bool
    {
        return false;
    }

    public function conditionForSlot(Identifier $identifier): ?ConditionInterface
    {
        return null;
    }

    public function identiferFromPathAndCondition(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value
    ): Identifier {
        return Identifier::NULL;
    }

    public function allSlotsForPath(PathInterface $path_to_element): \Generator
    {
        yield from [];
    }

    public function doesSlotExist(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value
    ): bool {
        return false;
    }

    public function dataTypeForSlot(Identifier $identifier): DataType
    {
        return DataType::NULL;
    }
}
