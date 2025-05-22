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

/**
 * Class ilADTLocalizedText
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilADTLocalizedTextDefinition extends ilADTDefinition
{
    /**
     * @var array
     */
    private array $active_languages = [];
    private string $default_language = '';
    private ?int $max_length = null;

    private bool $multilingual_value_support = false;

    public function getMaxLength(): ?int
    {
        return $this->max_length;
    }

    public function setMaxLength(?int $max_length): void
    {
        $this->max_length = $max_length;
    }

    public function setMultilingualValueSupport(bool $status): void
    {
        $this->multilingual_value_support = $status;
    }

    public function getMultilingualValueSupport(): bool
    {
        return $this->multilingual_value_support;
    }

    /**
     * @return string[]
     */
    public function getActiveLanguages(): array
    {
        return $this->active_languages;
    }

    public function setActiveLanguages(array $active): void
    {
        $this->active_languages = $active;
    }

    /**
     * @inheritDoc
     */
    public function isComparableTo(ilADT $a_adt): bool
    {
        return $a_adt instanceof ilADTLocalizedText;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage(): string
    {
        return $this->default_language;
    }

    /**
     * @param string $default_language
     */
    public function setDefaultLanguage(string $default_language): void
    {
        $this->default_language = $default_language;
    }

    /**
     * @return bool
     */
    public function supportsTranslations(): bool
    {
        return strlen($this->getDefaultLanguage()) > 0;
    }
}
