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
use ILIAS\AdvancedMetaData\Data\Exception;

class OptionImplementation extends PersistenceTrackingDataImplementation implements Option
{
    protected ?int $option_id;

    /**
     * @var OptionTranslation[]
     */
    protected array $translations;

    public function __construct(
        protected int $position,
        ?int $option_id = null,
        OptionTranslation ...$translations
    ) {
        $this->option_id = $option_id;
        $this->translations = $translations;
    }

    public function optionID(): ?int
    {
        return $this->option_id;
    }

    public function isPersisted(): bool
    {
        return !is_null($this->option_id);
    }

    protected function getSubData(): \Generator
    {
        yield from $this->getTranslations();
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        if ($this->position === $position) {
            return;
        }
        $this->position = $position;
        $this->markAsChanged();
    }

    public function getTranslations(): \Generator
    {
        yield from $this->translations;
    }

    public function hasTranslationInLanguage(string $language): bool
    {
        foreach ($this->translations as $translation) {
            if ($translation->language() === $language) {
                return true;
            }
        }
        return false;
    }

    public function getTranslationInLanguage(string $language): ?OptionTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->language() === $language) {
                return $translation;
            }
        }
        return null;
    }

    /**
     * @throws Exception if translation in this language already exists.
     */
    public function addTranslation(string $language): OptionTranslation
    {
        if ($this->hasTranslationInLanguage($language)) {
            throw new Exception('Translation in language ' . $language . ' already exists.');
        }

        $translation = new OptionTranslationImplementation($language, '');
        $this->translations[] = $translation;
        return $translation;
    }

    public function removeTranslation(string $language): void
    {
        foreach ($this->translations as $key => $translation) {
            if ($translation->language() !== $language) {
                continue;
            }
            unset($this->translations[$key]);
            $this->markAsChanged();
        }
    }
}
