<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT form bridge base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTFormBridge
{
    protected $adt; // [ilADT]
    protected $form; // [ilPropertyFormGUI]
    protected $id; // [string]
    protected $title; // [string]
    protected $info; // [string]
    protected $parent_element; // [string|array]
    protected $required; // [bool]
    protected $disabled; // [bool]
    
    /**
     * Constructor
     *
     * @param ilADT $a_adt
     * @return self
     */
    public function __construct(ilADT $a_adt)
    {
        $this->setADT($a_adt);
    }
    
    
    //
    // properties
    //
    
    /**
     * Check if given ADT is valid
     *
     * :TODO: This could be avoided with type-specifc constructors
     * :TODO: bridge base class?
     *
     * @param ilADT $a_adt
     */
    abstract protected function isValidADT(ilADT $a_adt);
    
    /**
     * Set ADT
     *
     * @throws Exception
     * @param ilADT $a_adt
     */
    protected function setADT(ilADT $a_adt)
    {
        if (!$this->isValidADT($a_adt)) {
            throw new Exception('ADTFormBridge Type mismatch.');
        }
        
        $this->adt = $a_adt;
    }
    
    /**
     * Get ADT
     *
     * @return ilADT
     */
    public function getADT()
    {
        return $this->adt;
    }
    
    /**
     * Set form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function setForm(ilPropertyFormGUI $a_form)
    {
        $this->form = $a_form;
    }
    
    /**
     * Get form
     *
     * @return ilPropertyFormGUI
     */
    public function getForm()
    {
        return $this->form;
    }
    
    /**
     * Set element id (aka form field)
     *
     * @param string $a_value
     */
    public function setElementId($a_value)
    {
        $this->id = (string) $a_value;
    }
    
    /**
     * Get element id
     *
     * @return string
     */
    public function getElementId()
    {
        return $this->id;
    }
    
    /**
     * Set title (aka form field caption)
     *
     * @param string $a_value
     */
    public function setTitle($a_value)
    {
        $this->title = trim($a_value);
    }
    
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set info (aka form field info text)
     *
     * @param string $a_value
     */
    public function setInfo($a_value)
    {
        $this->info = trim($a_value);
    }
    
    /**
     * Get info
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }
    
    /**
     * Set parent element
     *
     * @param string|array $a_value
     */
    public function setParentElement($a_value)
    {
        if (!is_array($a_value)) {
            $a_value = (string) $a_value;
        }
        $this->parent = $a_value;
    }
    
    /**
     * Get parent element
     *
     * @return string
     */
    public function getParentElement()
    {
        return $this->parent;
    }
    
    /**
     * Set disabled
     *
     * @param bool $a_value
     */
    public function setDisabled($a_value)
    {
        $this->disabled = (bool) $a_value;
    }
    
    /**
     * Get disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
    
    /**
     * Set required
     *
     * @param bool $a_value
     */
    public function setRequired($a_value)
    {
        $this->required = (bool) $a_value;
    }
    
    /**
     * Get required
     *
     * @return required
     */
    public function isRequired()
    {
        return $this->required;
    }
    
    
    //
    // form
    //
    
    /**
     * Helper method to handle generic properties like setRequired(), setInfo()
     *
     * @param ilFormPropertyGUI $a_field
     * @param ilADTDefinition $a_def
     */
    protected function addBasicFieldProperties(ilFormPropertyGUI $a_field, ilADTDefinition $a_def)
    {
        if ((bool) $this->isDisabled()) {
            $a_field->setDisabled(true);
        } elseif ($this->isRequired()) {
            $a_field->setRequired(true);
        }
        
        $info = $this->getInfo();
        if ($info) {
            $a_field->setInfo($info);
        }
    }
    
    /**
     * Try to find parent element in form (could be option)
     *
     * @return ilFormPropertyGUI
     */
    protected function findParentElementInForm()
    {
        $parent_def = $this->getParentElement();
        if ($parent_def) {
            if (is_array($parent_def)) {
                $parent_option = $parent_def[1];
                $parent_def = $parent_def[0];
            }

            // :TODO: throw exception on invalid definition ?!

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
    }
                
    /**
     * Add form field to parent element
     *
     * @param ilFormPropertyGUI $a_field
     */
    protected function addToParentElement(ilFormPropertyGUI $a_field)
    {
        $field = $this->findParentElementInForm();
        if ($field) {
            $field->addSubItem($a_field);
        } elseif ($this->getForm() instanceof ilPropertyFormGUI) {
            $this->getForm()->addItem($a_field);
        }
    }
    
    /**
     * Add ADT-specific fields to form
     */
    abstract public function addToForm();
    
    /**
     * Add ADT-specific JS-files to template
     *
     * @param ilTemplate $a_tpl
     */
    public function addJS(ilTemplate $a_tpl)
    {
    }
    
    /**
     * Check if element is currently active for subitem(s)
     *
     * @param mixed $a_parent_option
     * @return bool
     */
    protected function isActiveForSubItems($a_parent_option = null)
    {
        return !$this->getADT()->isNull();
    }
        
    /**
     * Check if incoming values should be imported at all
     *
     * @param ilADTFormBridge $a_parent_adt
     * @return bool
     */
    public function shouldBeImportedFromPost(ilADTFormBridge $a_parent_adt = null)
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
    abstract public function importFromPost();
    
    /**
     * Validate ADT and parse error codes
     *
     * @return boolean
     */
    public function validate()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        // ilADTFormBridge->isRequired() != ilADT->allowNull()
        if ($this->isRequired() && $this->getADT()->isNull()) {
            $field = $this->getForm()->getItemByPostvar($this->getElementId());
            $field->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        // no need to further validate if no value given
        elseif (!$this->getADT()->isValid()) {
            $tmp = array();
            
            $mess = $this->getADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getADT()->translateErrorCode($error_code);
            }
            
            $field = $this->getForm()->getItemByPostvar($this->getElementId());
            $field->setAlert(implode("<br />", $tmp));
            
            return false;
        }
        
        return true;
    }
    
    public function setExternalErrors($a_errors)
    {
        $field = $this->getForm()->getItemByPostvar($this->getElementId());
        $field->setAlert(implode("<br />", $a_errors));
    }
}
