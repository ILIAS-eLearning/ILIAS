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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\Type;

class NullSelectSpecificData implements SelectSpecificData
{
    public function hasOptions(): bool
    {
        return false;
    }

    public function isPersisted(): bool
    {
        return false;
    }

    public function containsChanges(): bool
    {
        return false;
    }

    public function getOptions(): \Generator
    {
        yield from [];
    }

    public function getOption(int $option_id): ?Option
    {
        return null;
    }

    public function removeOption(int $option_id): void
    {
    }

    public function addOption(): Option
    {
        return new NullOption();
    }

    public function isTypeSupported(Type $type): bool
    {
        return false;
    }

    public function fieldID(): ?int
    {
        return null;
    }
}
