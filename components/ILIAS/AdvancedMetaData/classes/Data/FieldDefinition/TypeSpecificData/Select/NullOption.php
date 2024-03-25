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

class NullOption implements Option
{
    public function optionID(): ?int
    {
        return null;
    }

    public function getPosition(): int
    {
        return 0;
    }

    public function setPosition(int $position): void
    {
    }

    public function getTranslations(): \Generator
    {
        yield from [];
    }

    public function hasTranslationInLanguage(string $language): bool
    {
        return false;
    }

    public function getTranslationInLanguage(string $language): ?OptionTranslation
    {
        return null;
    }

    public function addTranslation(string $language): OptionTranslation
    {
        return new NullOptionTranslation();
    }

    public function removeTranslation(string $language): void
    {
    }

    public function isPersisted(): bool
    {
        return false;
    }

    public function containsChanges(): bool
    {
        return false;
    }
}
