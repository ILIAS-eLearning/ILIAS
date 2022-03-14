<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT search bridge base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
abstract class ilADTSearchBridge
{
    public const SQL_STRICT = 1;
    public const SQL_LIKE = 2;
    public const SQL_LIKE_END = 3;
    public const SQL_LIKE_START = 4;
    public const DEFAULT_SEARCH_COLUMN = 'value';

    protected ilPropertyFormGUI $form;
    protected ilTable2GUI $table_gui;
    protected array $table_filter_fields = [];
    protected string $id = '';
    protected string $title = '';
    protected string $info = '';

    protected ilLanguage $lng;
    protected ilDBInterface $db;

    public function __construct(ilADTDefinition $a_adt_def)
    {
        global $DIC;
        $this->setDefinition($a_adt_def);

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
    }

    abstract protected function isValidADTDefinition(ilADTDefinition $a_adt_def) : bool;

    abstract protected function setDefinition(ilADTDefinition $a_adt_def) : void;

    abstract public function isNull() : bool;

    public function setForm(ilPropertyFormGUI $a_form) : void
    {
        $this->form = $a_form;
    }

    public function getForm() : ?ilPropertyFormGUI
    {
        return $this->form;
    }

    public function setElementId(string $a_value) : void
    {
        $this->id = $a_value;
    }

    public function getElementId() : string
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

    public function getSearchColumn() : string
    {
        return self::DEFAULT_SEARCH_COLUMN;
    }

    public function setTableGUI(ilTable2GUI $a_table) : void
    {
        $this->table_gui = $a_table;
    }

    /**
     * Get table gui
     */
    public function getTableGUI() : ?ilTable2GUI
    {
        return $this->table_gui;
    }

    /**
     * Write value(s) to filter store (in session)
     * @param mixed|null $a_value
     */
    protected function writeFilter(mixed $a_value = null) : void
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
     * @return mixed
     */
    protected function readFilter() : mixed
    {
        if (!$this->table_gui instanceof ilTable2GUI) {
            return null;
        }
        $value = $_SESSION["form_" . $this->table_gui->getId()][$this->getElementId()];
        if ($value) {
            return unserialize($value);
        }
        return '';
    }

    /**
     * Load filter value(s) into ADT
     */
    abstract public function loadFilter() : void;

    /**
     * Add form field to parent element
     * @param ilFormPropertyGUI $a_field
     */
    protected function addToParentElement(ilFormPropertyGUI $a_field) : void
    {
        if ($this->getForm() instanceof ilPropertyFormGUI) {
            $this->getForm()->addItem($a_field);
        } elseif ($this->getTableGUI() instanceof ilTable2GUI) {
            $this->table_filter_fields[$a_field->getFieldId()] = $a_field;
            $this->getTableGUI()->addFilterItem($a_field);
        }
    }

    /**
     * Add sub-element
     * @param string $a_add
     * @return string
     */
    protected function addToElementId(string $a_add) : string
    {
        return $this->getElementId() . "[" . $a_add . "]";
    }

    /**
     * Add ADT-specific fields to form
     */
    abstract public function addToForm() : void;

    /**
     * Check if incoming values should be imported at all
     * @param mixed $a_post
     * @return bool
     */
    protected function shouldBeImportedFromPost(mixed $a_post) : bool
    {
        return true;
    }

    /**
     * Extract data from (post) values
     * @param array $a_post
     * @return mixed
     */
    protected function extractPostValues(array $a_post = null) : mixed
    {
        $element_id = $this->getElementId();
        $multi = strpos($this->getElementId(), "[");

        // get rid of this case
        if ($a_post === null) {
            $a_post = $_POST;
            if ($multi !== false) {
                $post = $a_post[substr($element_id, 0, $multi)][substr($element_id, $multi + 1, -1)];
            } else {
                $post = $a_post[$element_id];
            }
        } elseif ($multi !== false) {
                $post = $a_post[substr($element_id, $multi + 1, -1)];
            } else {
                $post = $a_post[$element_id];
            }
        return $post;
    }

    /**
     * @todo make post required
     */
    abstract public function importFromPost(array $a_post = null) : bool;

    /**
     * Validate current data
     * @return bool
     */
    abstract public function validate() : bool;


    //
    // DB
    //

    /**
     * Get SQL condition for current value(s)
     */
    abstract public function getSQLCondition(
        string $a_element_id,
        int $mode = self::SQL_LIKE,
        array $quotedWords = []
    ) : string;

    /**
     * Compare directly against ADT
     */
    public function isInCondition(ilADT $a_adt) : bool
    {
        return false;
    }


    //
    //  import/export
    //

    /**
     * Get current value(s) in serialized form (for easy persisting)
     */
    abstract public function getSerializedValue() : string;

    /**
     * Set current value(s) in serialized form (for easy persisting)
     */
    abstract public function setSerializedValue(string $a_value) : void;
}
