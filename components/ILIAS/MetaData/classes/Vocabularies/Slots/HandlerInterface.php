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
use ILIAS\MetaData\Elements\Data\Type as DataType;

interface HandlerInterface
{
    public function pathForSlot(Identifier $identifier): PathInterface;

    public function isSlotConditional(Identifier $identifier): bool;

    public function conditionForSlot(Identifier $identifier): ?ConditionInterface;

    public function identiferFromPathAndCondition(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value
    ): Identifier;

    /**
     * @return Identifier[]
     */
    public function allSlotsForPath(PathInterface $path_to_element): \Generator;

    public function doesSlotExist(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value
    ): bool;

    public function dataTypeForSlot(Identifier $identifier): DataType;
}
