<?php declare(strict_types=1);

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
    protected bool $multi = false;
    protected ?int $multi_rows;
    protected ?int $multi_cols;

    public function __construct(ilADT $a_adt)
    {
        parent::__construct($a_adt);
        $this->lng->loadLanguageModule('meta');
    }

    public function setMulti(bool $a_value, ?int $a_cols = null, ?int $a_rows = null) : void
    {
        $this->multi = $a_value;
        $this->multi_rows = ($a_rows === null) ? null : $a_rows;
        $this->multi_cols = ($a_cols === null) ? null : $a_cols;
    }

    public function isMulti() : bool
    {
        return $this->multi;
    }

    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTText);
    }

    protected function addElementToForm(
        string $title,
        string $element_id,
        string $value,
        bool $is_translation = false,
        string $language = ''
    ) : void {
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
            $text->setInfo($this->lng->txt('md_adv_int_translation_info') . ' ' . $this->lng->txt('meta_l_' . $language));
            $text->setRequired(false);
        }
        $text->setValue($value);
        $this->addToParentElement($text);
    }

    public function addToForm() : void
    {
        $this->addElementToForm(
            (string) $this->getTitle(),
            (string) $this->getElementId(),
            (string) $this->getADT()->getText()
        );
    }

    public function importFromPost() : void
    {
        // ilPropertyFormGUI::checkInput() is pre-requisite
        $this->getADT()->setText($this->getForm()->getInput($this->getElementId()));
        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setValue($this->getADT()->getText());
    }
}
