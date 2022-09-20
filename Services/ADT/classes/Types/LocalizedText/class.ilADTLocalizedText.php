<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        return !$this->getLength() && !count($this->getTranslations());
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
