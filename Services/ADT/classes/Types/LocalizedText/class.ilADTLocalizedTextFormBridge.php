<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilADTLocalizedTextDBBridge
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilADTLocalizedTextFormBridge extends ilADTTextFormBridge
{
    /**
     * @inheritDoc
     */
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return $a_adt instanceof ilADTLocalizedText;
    }

    /**
     * @inheritDoc
     */
    public function addToForm() : void
    {
        $active_languages = $this->getADT()->getCopyOfDefinition()->getActiveLanguages();

        if (!count($active_languages)) {
            $this->addElementToForm(
                $this->getTitle(),
                (string) $this->getElementId(),
                (string) $this->getADT()->getText(),
                false,
                ''
            );
            return;
        }
        $is_translation = null;
        foreach ($active_languages as $active_language) {
            if (strcmp($active_language, $this->getADT()->getCopyOfDefinition()->getDefaultLanguage()) === 0) {
                $is_translation = false;
            } else {
                $is_translation = true;
            }
            
            $languages = $this->getADT()->getTranslations();
            
            $text = '';
            
            if (array_key_exists($active_language, $languages)) {
                $text = $languages[$active_languages];
            }
            
            $this->addElementToForm(
                $this->getTitle(),
                $this->getElementId() . '_' . $active_language,
                $text,
                $is_translation,
                $active_language
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function importFromPost() : void
    {
        if (!$this->getADT()->getCopyOfDefinition()->supportsTranslations()) {
            parent::importFromPost();
            return;
        }
        $active_languages = $this->getADT()->getCopyOfDefinition()->getActiveLanguages();
        foreach ($active_languages as $language) {
            $this->getADT()->setTranslation(
                $language,
                $this->getForm()->getInput($this->getElementId() . '_' . $language)
            );
            $this->getADT()->setText($this->getForm()->getInput($this->getElementId() . '_' . $language));
            $input_item = $this->getForm()->getItemByPostVar($this->getElementId() . '_' . $language);
            $input_item->setValue((string) $this->getADT()->getTranslations()[$language]);
        }
    }
}
