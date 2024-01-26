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

class ilADTTextFormBridge extends ilADTFormBridge
{
    protected $multi; // [bool]
    protected $multi_rows; // [int]
    protected $multi_cols; // [int]

    /**
     * @var ilLanguage|null
     */
    private $language = null;

    public function __construct(ilADT $a_adt)
    {
        global $DIC;

        parent::__construct($a_adt);
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('meta');
    }


    //
    // properties
    //
    
    /**
     * Set multi-line
     *
     * @param string $a_value
     * @param int $a_cols
     * @param int $a_rows
     */
    public function setMulti($a_value, $a_cols = null, $a_rows = null)
    {
        $this->multi = (bool) $a_value;
        $this->multi_rows = ($a_rows === null) ? null : (int) $a_rows;
        $this->multi_cols = ($a_cols === null) ? null : (int) $a_cols;
    }

    /**
     * Is multi-line?
     *
     * @return bool
     */
    public function isMulti()
    {
        return $this->multi;
    }
    
    
    //
    // form
    //
    
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTText);
    }

    /**
     * @param string $title
     * @param string $element_id
     * @param string $value
     * @param bool   $is_translation
     * @param string $language
     */
    protected function addElementToForm(string $title, string $element_id, string $value, bool $is_translation = false,  string $language = '')
    {
        $def = $this->getADT()->getCopyOfDefinition();

        if (!$this->isMulti()) {
            $text = new ilTextInputGUI($title, $element_id);

            if ($def->getMaxLength()) {
                $max = $def->getMaxLength();
                $size = $text->getSize();

                $text->setMaxLength($max);

                if ($size && $max < $size) {
                    $text->setSize($max);
                }
            }
        } else {
            $text = new ilTextAreaInputGUI($title, $element_id);
            if ($this->multi_rows) {
                $text->setRows($this->multi_rows);
            }
            if ($this->multi_cols) {
                $text->setCols($this->multi_cols);
            }

            if ($def->getMaxLength()) {
                $max = $def->getMaxLength();
                $text->setMaxNumOfChars($max);
            }
        }
        $this->addBasicFieldProperties($text, $def);

        if ($is_translation) {
            $text->setInfo($this->language->txt('md_adv_int_translation_info') . ' ' . $this->language->txt('meta_l_' . $language));
            $text->setRequired(false);
        }
        $text->setValue($value);
        $this->addToParentElement($text);
    }

    public function addToForm()
    {
        $this->addElementToForm(
            (string) $this->getTitle(),
            (string) $this->getElementId(),
            (string) $this->getADT()->getText()
        );
    }

    public function importFromPost()
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setText($this->getForm()->getInput($this->getElementId()));
        $field = $this->getForm()->getItemByPostvar($this->getElementId());
        $field->setValue($this->getADT()->getText());
    }
}
