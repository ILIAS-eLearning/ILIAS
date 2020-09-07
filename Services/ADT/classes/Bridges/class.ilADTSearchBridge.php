<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

/**
 * ADT search bridge base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTSearchBridge
{
    protected $form; // [ilPropertyFormGUI]
    protected $table_gui; // [ilTable2GUI]
    protected $table_filter_fields = []; // [array]
    protected $id; // [string]
    protected $title; // [string]
    protected $info; // [string]
    
    /**
     * Constructor
     *
     * @param ilADT $a_adt_def
     * @return self
     */
    public function __construct(ilADTDefinition $a_adt_def)
    {
        $this->setDefinition($a_adt_def);
    }
    
    
    //
    // properties
    //
    
    /**
     * Check if given ADT definition is valid
     *
     * :TODO: This could be avoided with type-specifc constructors
     * :TODO: bridge base class?
     *
     * @param ilADTDefinition $a_adt_def
     */
    abstract protected function isValidADTDefinition(ilADTDefinition $a_adt_def);
    
    /**
     * Set ADT definition
     *
     * @param ilADTDefinition $a_adt_def
     */
    abstract protected function setDefinition(ilADTDefinition $a_adt_def);
    
    /**
     * Is null ?
     *
     * @return bool
     */
    abstract public function isNull();
    
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
    
    
    //
    // table2gui / filter
    //
    
    /**
     * Set table gui (for filter mode)
     *
     * @param ilTable2GUI $a_table
     */
    public function setTableGUI(ilTable2GUI $a_table)
    {
        $this->table_gui = $a_table;
    }
    
    /**
     * Get table gui
     *
     * @return ilTable2GUI
     */
    public function getTableGUI()
    {
        return $this->table_gui;
    }
    
    /**
     * Write value(s) to filter store (in session)
     *
     * @param mixed  $a_value
     */
    protected function writeFilter($a_value = null)
    {
        if (!$this->table_gui instanceof ilTable2GUI) {
            return;
        }
        if ($a_value !== null) {
            $_SESSION["form_" . $this->table_gui->getId()][$this->getElementId()] = serialize($a_value);
        } else {
            unset($_SESSION["form_" . $this->table_gui->getId()][$this->getElementId()]);
        }
    }
    
    /**
     * Load value(s) from filter store (in session)
     *
     * @param string $a_element_id
     */
    protected function readFilter()
    {
        if (!$this->table_gui instanceof ilTable2GUI) {
            return;
        }
        $value = $_SESSION["form_" . $this->table_gui->getId()][$this->getElementId()];
        if ($value) {
            return unserialize($value);
        }
    }
    
    /**
     * Load filter value(s) into ADT
     */
    abstract public function loadFilter();
    
    
    //
    // form
    //
    
    /**
     * Add form field to parent element
     *
     * @param ilFormPropertyGUI $a_field
     */
    protected function addToParentElement(ilFormPropertyGUI $a_field)
    {
        if ($this->getForm() instanceof ilPropertyFormGUI) {
            $this->getForm()->addItem($a_field);
        } elseif ($this->getTableGUI() instanceof ilTable2GUI) {
            $this->table_filter_fields[$a_field->getFieldId()] = $a_field;
            $this->getTableGUI()->addFilterItem($a_field);
            
            // :TODO:
            // $a_field->readFromSession();
            // $this->getTableGUI()->filter[$this->getElementId()] = $a_field->getValue();
        }
    }
    
    /**
     * Add sub-element
     *
     * @param string $a_add
     * @return string
     */
    protected function addToElementId($a_add)
    {
        return $this->getElementId() . "[" . $a_add . "]";
    }
    
    /**
     * Add ADT-specific fields to form
     */
    abstract public function addToForm();
    
    
    //
    // post data
    //
    
    /**
     * Check if incoming values should be imported at all
     *
     * @param mixed $a_post
     * @return bool
     */
    protected function shouldBeImportedFromPost($a_post)
    {
        return true;
    }
    
    /**
     * Extract data from (post) values
     *
     * @param array $a_post
     * @return mixed
     */
    protected function extractPostValues(array $a_post = null)
    {
        $element_id = $this->getElementId();
        $multi = strpos($this->getElementId(), "[");
        
        if (!$a_post) {
            $a_post = $_POST;
            if ($multi !== false) {
                $post = $a_post[substr($element_id, 0, $multi)][substr($element_id, $multi + 1, -1)];
            } else {
                $post = $a_post[$element_id];
            }
        } else {
            if ($multi !== false) {
                $post = $a_post[substr($element_id, $multi + 1, -1)];
            } else {
                $post = $a_post[$element_id];
            }
        }
        
        return $post;
    }
    
    /**
     * Import values from (search) form request POST data
     *
     * @return bool
     */
    abstract public function importFromPost(array $a_post = null);
    
    /**
     * Validate current data
     *
     * @return bool
     */
    abstract public function validate();
        
    
    //
    // DB
    //
    
    /**
     * Get SQL condition for current value(s)
     *
     * @param string $a_element_id
     * @return string
     */
    abstract public function getSQLCondition($a_element_id);
    
    /**
     * Compare directly against ADT
     *
     * @param ilADT $a_adt
     * @return boolean
     */
    public function isInCondition(ilADT $a_adt)
    {
        return false;
    }
    
    
    //
    //  import/export
    //
    
    /**
     * Get current value(s) in serialized form (for easy persisting)
     *
     * @return string
     */
    abstract public function getSerializedValue();
    
    /**
     * Set current value(s) in serialized form (for easy persisting)
     *
     * @param string
     */
    abstract public function setSerializedValue($a_value);
}
