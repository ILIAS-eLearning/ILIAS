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
class ilADTLocalizedText extends ilADTText
{
    private array $translations = [];

    public function getTextForLanguage(string $language): string
    {
        if (array_key_exists($language, $this->getTranslations()) && strlen($this->getTranslations()[$language])) {
            return $this->getTranslations()[$language];
        }
        return (string) $this->getText();
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setTranslation(string $language, string $translation): void
    {
        $this->translations[$language] = $translation;
    }

    /**
     * @inheritDoc
     */
    protected function isValidDefinition(ilADTDefinition $a_def): bool
    {
        return $a_def instanceof ilADTLocalizedTextDefinition;
    }

    /**
     * @inheritDoc
     */
    public function equals(ilADT $a_adt): ?bool
    {
        if (!$this->getDefinition()->isComparableTo($a_adt)) {
            return null;
        }
        if ($this->getTranslations() != count($a_adt->getTranslations())) {
            return false;
        }
        foreach ($a_adt->getTranslations() as $key => $value) {
            if (!isset($this->getTranslations()[$key])) {
                return false;
            }
            if (!strcmp($this->getTranslations()[$key], $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isLarger(ilADT $a_adt): ?bool
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isSmaller(ilADT $a_adt): ?bool
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isNull(): bool
    {
        $has_translation = false;
        foreach ($this->getTranslations() as $translation) {
            if ($translation !== '') {
                $has_translation = true;
                break;
            }
        }
        return !$this->getLength() && !$has_translation;
    }

    /**
     * @inheritDoc
     */
    public function getCheckSum(): ?string
    {
        if (!$this->isNull()) {
            return md5(serialize($this->getTranslations()));
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function exportStdClass(): ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->translations = $this->getTranslations();
            return $obj;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function importStdClass(?stdClass $a_std): void
    {
        if (is_object($a_std)) {
            $this->translations = $a_std->translations;
        }
    }
}
