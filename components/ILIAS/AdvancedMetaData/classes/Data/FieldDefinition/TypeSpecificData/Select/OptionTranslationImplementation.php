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

use ILIAS\AdvancedMetaData\Data\PersistenceTrackingDataImplementation;

class OptionTranslationImplementation extends PersistenceTrackingDataImplementation implements OptionTranslation
{
    public function __construct(
        protected string $language,
        protected string $value,
        protected bool $is_persisted = false
    ) {
    }

    public function isPersisted(): bool
    {
        return $this->is_persisted;
    }

    protected function getSubData(): \Generator
    {
        yield from [];
    }

    public function language(): string
    {
        return $this->language;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        if ($this->value === $value) {
            return;
        }
        $this->value = $value;
        $this->markAsChanged();
    }
}
