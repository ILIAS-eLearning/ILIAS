<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilADTLocalizedText
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilADTLocalizedText extends ilADTText
{
    /**
     * @var array
     */
    private $translations = [];

    /**
     * @param string $language
     */
    public function getTextForLanguage(string $language)
    {
        if (strlen($this->getTranslations()[$language])) {
            return $this->getTranslations()[$language];
        }
        return $this->getText();
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $language
     * @param string $translation
     */
    public function setTranslation(string $language, string $translation)
    {
        $this->translations[$language] = $translation;
    }

    /**
     * @inheritDoc
     */
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return $a_def instanceof ilADTLocalizedTextDefinition;
    }

    /**
     * @inheritDoc
     */
    public function equals(ilADT $adt)
    {
        if (!$this->getDefinition()->isComparableTo($adt)) {
            return false;
        }
        if (count($this->getTranslations()) != count($adt->getTranslations())) {
            return false;
        }
        foreach ($adt->getTranslations() as $key => $value) {
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
    public function isLarger(ilADT $a_adt)
    {
    }

    /**
     * @inheritDoc
     */
    public function isSmaller(ilADT $a_adt)
    {
    }

    /**
     * @inheritDoc
     */
    public function isNull()
    {
        return !$this->getLength() && !count($this->getTranslations());
    }

    /**
     * @inheritDoc
     */
    public function getCheckSum()
    {
        return md5(serialize($this->getTranslations()));
    }

    /**
     * @inheritDoc
     */
    public function exportStdClass()
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->translations = $this->getTranslations();
            return $obj;
        }
    }

    /**
     * @inheritDoc
     */
    public function importStdClass($a_std)
    {
        if (is_object($a_std)) {
            $this->translations = $a_std->translations;
        }
    }
}