<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    private int $max_length;

    public function getMaxLength() : ?int
    {
        return $this->max_length;
    }

    public function setMaxLength(int $max_length) : void
    {
        $this->max_length = $max_length;
    }

    /**
     * @return string[]
     */
    public function getActiveLanguages() : array
    {
        return $this->active_languages;
    }

    public function setActiveLanguages(array $active) : void
    {
        $this->active_languages = $active;
    }

    /**
     * @inheritDoc
     */
    public function isComparableTo(ilADT $a_adt) : bool
    {
        return $a_adt instanceof ilADTLocalizedText;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage() : string
    {
        return $this->default_language;
    }

    /**
     * @param string $default_language
     */
    public function setDefaultLanguage(string $default_language) : void
    {
        $this->default_language = $default_language;
    }

    /**
     * @return bool
     */
    public function supportsTranslations() : bool
    {
        return strlen($this->getDefaultLanguage()) > 0;
    }
}
