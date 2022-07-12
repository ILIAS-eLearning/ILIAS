<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT form bridge base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTFormBridge
{
    protected ilADT $adt;
    protected ilPropertyFormGUI $form;
    /**
     * @var mixed
     */
    protected $parent;
    protected ?string $id = null;
    protected string $title = '';
    protected string $info = '';
    protected $parent_element; // [string|array]
    protected bool $required = false;
    protected bool $disabled = false;

    protected ilLanguage $lng;

    public function __construct(ilADT $a_adt)
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->setADT($a_adt);
    }

    abstract protected function isValidADT(ilADT $a_adt) : bool;

    protected function setADT(ilADT $a_adt) : void
    {
        if (!$this->isValidADT($a_adt)) {
            throw new InvalidArgumentException('ADTFormBridge Type mismatch.');
        }
        $this->adt = $a_adt;
    }

    public function getADT() : ilADT
    {
        return $this->adt;
    }

    public function setForm(ilPropertyFormGUI $a_form) : void
    {
        $this->form = $a_form;
    }

    public function getForm() : ?ilPropertyFormGUI
    {
        return $this->form;
    }

    /**
     * Set element id (aka form field)
     * @param string $a_value
     */
    public function setElementId(string $a_value) : void
    {
        $this->id = $a_value;
    }

    public function getElementId() : ?string
    {
        return $this->id;
    }

    public function setTitle(string $a_value) : void
    {
        $this->title = trim($a_value);
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setInfo(string $a_value) : void
    {
        $this->info = trim($a_value);
    }

    public function getInfo() : string
    {
        return $this->info;
    }

    /**
     * @param mixed $a_value
     */
    public function setParentElement($a_value) : void
    {
        if (!is_array($a_value)) {
            $a_value = (string) $a_value;
        }
        $this->parent = $a_value;
    }

    /**
     * Get parent element
     * @return mixed
     */
    public function getParentElement()
    {
        return $this->parent;
    }

    public function setDisabled(bool $a_value) : void
    {
        $this->disabled = $a_value;
    }

    public function isDisabled() : bool
    {
        return $this->disabled;
    }

    public function setRequired(bool $a_value) : void
    {
        $this->required = $a_value;
    }

    public function isRequired() : bool
    {
        return $this->required;
    }

    /**
     * Helper method to handle generic properties like setRequired(), setInfo()
     * @param ilFormPropertyGUI $a_field
     * @param ilADTDefinition   $a_def
     */
    protected function addBasicFieldProperties(ilFormPropertyGUI $a_field, ilADTDefinition $a_def) : void
    {
        if ($this->isDisabled()) {
            $a_field->setDisabled(true);
        } elseif ($this->isRequired()) {
            $a_field->setRequired(true);
        }

        $info = $this->getInfo();
        if ($info) {
            $a_field->setInfo($info);
        }
    }

    protected function findParentElementInForm() : ?ilFormPropertyGUI
    {
        $parent_def = $this->getParentElement();
        $parent_option = '';
        if ($parent_def) {
            if (is_array($parent_def)) {
                $parent_option = $parent_def[1];
                $parent_def = $parent_def[0];
            }
            $parent_field = $this->getForm()->getItemByPostVar($parent_def);
            if ($parent_field instanceof ilSubEnabledFormPropertyGUI) {
                // radio/checkbox group
                if ($parent_option && method_exists($parent_field, "getOptions")) {
                    foreach ($parent_field->getOptions() as $option) {
                        if ($option->getValue() == $parent_option) {
                            $parent_field = $option;
                            break;
                        }
                    }
                }
            }

            if ($parent_field) {
                return $parent_field;
            }
        }
        return null;
    }

    protected function addToParentElement(ilFormPropertyGUI $a_field) : void
    {
        $field = $this->findParentElementInForm();
        if ($field instanceof ilSubEnabledFormPropertyGUI) {
            $field->addSubItem($a_field);
        } elseif ($this->getForm() instanceof ilPropertyFormGUI) {
            $this->getForm()->addItem($a_field);
        }
    }

    /**
     * Add ADT-specific fields to form
     */
    abstract public function addToForm() : void;

    /**
     * Add ADT-specific JS-files to template
     * @param ilGlobalTemplate $a_tpl
     */
    public function addJS(ilGlobalTemplateInterface $a_tpl) : void
    {
    }

    /**
     * Check if element is currently active for subitem(s)
     * @param mixed|null $a_parent_option
     * @return bool
     */
    protected function isActiveForSubItems($a_parent_option = null) : bool
    {
        return !$this->getADT()->isNull();
    }

    /**
     * Check if incoming values should be imported at all
     * @param ilADTFormBridge $a_parent_adt
     * @return bool
     */
    public function shouldBeImportedFromPost(ilADTFormBridge $a_parent_adt = null) : bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        // inactive parent elements disable importing
        if ($a_parent_adt) {
            $parent_option = null;
            $parent_element = $this->getParentElement();
            if (is_array($parent_element)) {
                $parent_option = $parent_element[1];
            }
            return $a_parent_adt->isActiveForSubItems($parent_option);
        }
        return true;
    }

    /**
     * Import values from form request POST data
     */
    abstract public function importFromPost() : void;

    public function validate() : bool
    {
        // ilADTFormBridge->isRequired() != ilADT->allowNull()
        if ($this->isRequired() && $this->getADT()->isNull()) {
            $field = $this->getForm()->getItemByPostVar($this->getElementId());
            $field->setAlert($this->lng->txt("msg_input_is_required"));
            return false;
        } // no need to further validate if no value given
        elseif (!$this->getADT()->isValid()) {
            $tmp = [];
            $mess = $this->getADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getADT()->translateErrorCode($error_code);
            }
            $field = $this->getForm()->getItemByPostVar($this->getElementId());
            $field->setAlert(implode("<br />", $tmp));
            return false;
        }
        return true;
    }

    public function setExternalErrors(array $a_errors) : void
    {
        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setAlert(implode("<br />", $a_errors));
    }
}
