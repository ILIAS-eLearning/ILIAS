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

namespace ILIAS\GlobalScreen\UI\Footer\Translation;

class Translations
{
    /**
     * @var Translation[]
     */
    private array $translations = [];

    public function __construct(
        private readonly string $default_language_code,
        private readonly TranslatableItem $item,
        Translation ...$translations
    ) {
        foreach ($translations as $translation) {
            $this->add($translation);
        }
    }

    public function getId(): string
    {
        return $this->item->getId();
    }

    public function get(): array
    {
        return $this->translations;
    }

    public function add(Translation $translation): void
    {
        $this->translations[$translation->getLanguageCode()] = $translation;
    }

    public function remove(string $language_code): void
    {
        unset($this->translations[$language_code]);
    }

    public function getLanguageCode(string $language_code): ?Translation
    {
        return $this->translations[$language_code] ?? null;
    }

    public function getDefault(): ?Translation
    {
        return $this->translations[$this->default_language_code] ?? null;
    }

    public function getLanguageKeys(): array
    {
        return array_keys($this->translations);
    }

}
