<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * @ingroup ModulesDataCollection
 */
class ilDclBaseFieldModel
{

    /**
     * @var mixed int for custom fields string for standard fields
     */
    protected $id;
    /**
     * @var int
     */
    protected $table_id;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var int
     */
    protected $datatypeId;
    /**
     * @var bool
     */
    protected $required;
    /**
     * @var int
     */
    protected $order;
    /**
     * @var bool
     */
    protected $unique;
    /**
     * @var bool
     */
    protected $locked;
    /**
     * @var array
     */
    protected $property = array();
    /**
     * @var bool
     */
    protected $exportable;
    /**
     * @var ilDclDatatype This fields Datatype.
     */
    protected $datatype;
    /**
     * @var null|int With this property the datatype-storage-location can be overwritten. This need to be done in plugins.
     */
    protected $storage_location_override = null;
    /**
     * General properties
     */
    const PROP_LENGTH = "lenght";
    const PROP_REGEX = "regex";
    const PROP_REFERENCE = "table_id";
    const PROP_URL = "url";
    const PROP_TEXTAREA = "text_area";
    const PROP_REFERENCE_LINK = "reference_link";
    const PROP_WIDTH = "width";
    const PROP_HEIGHT = "height";
    const PROP_LEARNING_PROGRESS = "learning_progress";
    const PROP_ILIAS_REFERENCE_LINK = "ILIAS_reference_link";
    const PROP_N_REFERENCE = "multiple_selection";
    const PROP_FORMULA_EXPRESSION = "expression";
    const PROP_DISPLAY_COPY_LINK_ACTION_MENU = "display_action_menu";
    const PROP_LINK_DETAIL_PAGE_TEXT = "link_detail_page";
    const PROP_SUPPORTED_FILE_TYPES = "supported_file_types";
    const PROP_PLUGIN_HOOK_NAME = "plugin_hook_name";
    const PROP_TEXT_SELECTION_OPTIONS = "text_selection_options";
    const PROP_TEXT_SELECTION_TYPE = "text_selection_type";
    const PROP_DATE_SELECTION_OPTIONS = "date_selection_options";
    const PROP_DATE_SELECTION_TYPE = "date_selection_type";
    // type of table il_dcl_view
    const EDIT_VIEW = 2;
    const EXPORTABLE_VIEW = 4;


    /**
     * @param int $a_id
     */
    public function __construct($a_id = 0)
    {
        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }
    }


    /**
     * All valid chars for filed titles
     *
     * @param bool $a_as_regex
     *
     * @return string
     */
    public static function _getTitleInvalidChars($a_as_regex = true)
    {
        if ($a_as_regex) {
            return '/^[^<>\\\\":]*$/i';
        } else {
            return '\ < > " :';
        }
    }


    /**
     * @param $title    Title of the field
     * @param $table_id ID of table where the field belongs to
     *
     * @return int
     */
    public static function _getFieldIdByTitle($title, $table_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query(
            'SELECT id FROM il_dcl_field WHERE title = ' . $ilDB->quote($title, 'text') . ' AND table_id = '
            . $ilDB->quote($table_id, 'integer')
        );
        $id = 0;
        while ($rec = $ilDB->fetchAssoc($result)) {
            $id = $rec['id'];
        }

        return $id;
    }


    /**
     * Set field id
     *
     * @param int $a_id
     */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }


    /**
     * Get field id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set table id
     *
     * @param int $a_id
     */
    public function setTableId($a_id)
    {
        $this->table_id = $a_id;
    }


    /**
     * Get table id
     *
     * @return int
     */
    public function getTableId()
    {
        return $this->table_id;
    }


    /**
     * Set title
     *
     * @param string $a_title
     */
    public function setTitle($a_title)
    {
        //title cannot begin with _ as this is saved for other purposes. make __ instead.
        if (substr($a_title, 0, 1) == "_" && substr($a_title, 0, 2) != "__") {
            $a_title = "_" . $a_title;
        }
        $this->title = $a_title;
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
     * Set description
     *
     * @param string $a_desc
     */
    public function setDescription($a_desc)
    {
        $this->description = $a_desc;
    }


    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set datatype id
     *
     * @param int $a_id
     */
    public function setDatatypeId($a_id)
    {
        //unset the cached datatype.
        $this->datatype = null;
        $this->datatypeId = $a_id;
    }


    /**
     * Get datatype_id
     *
     * @return int
     */
    public function getDatatypeId()
    {
        if ($this->isStandardField()) {
            return ilDclStandardField::_getDatatypeForId($this->getId());
        }

        return $this->datatypeId;
    }


    /**
     * Set Required
     *
     * @param boolean $a_required Required
     */
    public function setRequired($a_required)
    {
        $this->required = $a_required;
    }


    /**
     * Get Required Required
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }


    /**
     * @return bool
     */
    public function isUnique()
    {
        return $this->unique;
    }


    /**
     * @param bool $unique
     */
    public function setUnique($unique)
    {
        $this->unique = $unique ? 1 : 0;
    }


    /**
     * @return ilDclDatatype
     */
    public function getDatatype()
    {
        $this->loadDatatype();

        return $this->datatype;
    }


    /**
     * @return string
     */
    public function getDatatypeTitle()
    {
        $this->loadDatatype();

        return $this->datatype->getTitle();
    }


    /**
     * Get storage location for the model
     *
     * @return int|null
     */
    public function getStorageLocation()
    {
        if ($this->getStorageLocationOverride() !== null) {
            return $this->getStorageLocationOverride();
        }

        $this->loadDatatype();

        return $this->datatype->getStorageLocation();
    }


    /**
     * Load datatype for model
     */
    protected function loadDatatype()
    {
        if ($this->datatype == null) {
            $this->datatype = ilDclCache::getDatatype($this->datatypeId);
        }
    }


    /**
     * loadTableFieldSetting
     */
    protected function loadTableFieldSetting()
    {
        $tablefield_setting = ilDclTableFieldSetting::getInstance($this->getTableId(), $this->getId());
        $this->exportable = $tablefield_setting->isExportable();
        $this->order = $tablefield_setting->getFieldOrder();
    }


    /**
     * @return bool
     */
    public function getExportable()
    {
        if (!isset($this->exportable)) {
            $this->loadExportability();
        }

        return $this->exportable;
    }


    /**
     * Load exportability
     */
    private function loadExportability()
    {
        if ($this->exportable == null) {
            $this->loadTableFieldSetting();
        }
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return (array) $this;
    }


    /**
     * @return bool
     */
    public function isStandardField()
    {
        return false;
    }


    /**
     * Read field
     */
    public function doRead()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        //THEN 1 ELSE 0 END AS has_options FROM il_dcl_field f WHERE id = ".$ilDB->quote($this->getId(),"integer");
        $query = "SELECT * FROM il_dcl_field WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setTableId($rec["table_id"]);
        $this->setTitle($rec["title"]);
        $this->setDescription($rec["description"]);
        $this->setDatatypeId($rec["datatype_id"]);
        $this->setRequired($rec["required"]);
        $this->setUnique($rec["is_unique"]);
        $this->setLocked($rec["is_locked"]);
        $this->loadProperties();
        $this->loadTableFieldSetting();
    }


    /**
     * Builds model from db record
     *
     * @param $rec
     */
    public function buildFromDBRecord($rec)
    {
        $this->setId($rec["id"]);
        $this->setTableId($rec["table_id"]);
        $this->setTitle($rec["title"]);
        $this->setDescription($rec["description"]);
        $this->setDatatypeId($rec["datatype_id"]);
        $this->setRequired($rec["required"]);
        $this->setUnique($rec["is_unique"]);
        $this->setLocked($rec["is_locked"]);
    }


    /**
     * Create new field
     */
    public function doCreate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $this->getLocked() == null ? $this->setLocked(false) : true;

        if (!ilDclTable::_tableExists($this->getTableId())) {
            throw new ilException("The field does not have a related table!");
        }

        $id = $ilDB->nextId("il_dcl_field");
        $this->setId($id);
        $query = "INSERT INTO il_dcl_field (" . "id" . ", table_id" . ", datatype_id" . ", title" . ", description" . ", required" . ", is_unique"
            . ", is_locked" . " ) VALUES (" . $ilDB->quote($this->getId(), "integer") . "," . $ilDB->quote($this->getTableId(), "integer") . ","
            . $ilDB->quote($this->getDatatypeId(), "integer") . "," . $ilDB->quote($this->getTitle(), "text") . ","
            . $ilDB->quote($this->getDescription(), "text") . "," . $ilDB->quote($this->getRequired(), "integer") . ","
            . $ilDB->quote($this->isUnique(), "integer") . "," . $ilDB->quote($this->getLocked() ? 1 : 0, "integer") . ")";
        $ilDB->manipulate($query);

        $this->updateTableFieldSetting();

        $this->addToTableViews();
    }


    /**
     * create ilDclTableViewFieldSettings for this field in each tableview
     */
    protected function addToTableViews()
    {
        foreach (ilDclTableView::getAllForTableId($this->table_id) as $tableview) {
            $tableview->createFieldSetting($this->id);
        }
    }


    /**
     * Update field
     */
    public function doUpdate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->update(
            "il_dcl_field",
            array(
            "table_id" => array(
                "integer",
                $this->getTableId(),
            ),
            "datatype_id" => array(
                "text",
                $this->getDatatypeId(),
            ),
            "title" => array(
                "text",
                $this->getTitle(),
            ),
            "description" => array(
                "text",
                $this->getDescription(),
            ),
            "required" => array(
                "integer",
                $this->getRequired(),
            ),
            "is_unique" => array(
                "integer",
                $this->isUnique(),
            ),
            "is_locked" => array(
                "integer",
                $this->getLocked() ? 1 : 0,
            ),
        ),
            array(
                "id" => array(
                    "integer",
                    $this->getId(),
                ),
            )
        );
        $this->updateTableFieldSetting();
        $this->updateProperties();
    }


    /**
     * Update properties of this field in Database
     */
    public function updateProperties()
    {
        foreach ($this->property as $prop) {
            $prop->store();
        }
    }


    /**
     * update exportable and fieldorder
     *
     */
    protected function updateTableFieldSetting()
    {
        $tablefield_setting = ilDclTableFieldSetting::getInstance($this->getTableId(), $this->getId());
        $tablefield_setting->setExportable($this->exportable);
        $tablefield_setting->setFieldOrder($this->order);
        $tablefield_setting->store();
    }


    /**
     * Remove field and properties
     */
    public function doDelete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // delete tablefield setting.
        ilDclTableFieldSetting::getInstance($this->getTableId(), $this->getId())->delete();

        $query = "DELETE FROM il_dcl_field_prop WHERE field_id = " . $ilDB->quote($this->getId(), "text");
        $ilDB->manipulate($query);

        $query = "DELETE FROM il_dcl_field WHERE id = " . $ilDB->quote($this->getId(), "text");
        $ilDB->manipulate($query);

        foreach ($this->getFieldSettings() as $field_setting) {
            $field_setting->delete();
        }
    }


    public function getFieldSettings()
    {
        return ilDclTableViewFieldSetting::where(array('field' => $this->getId()))->get();
    }


    /**
     * @return int
     */
    public function getOrder()
    {
        if ($this->order == null) {
            $this->loadTableFieldSetting();
        }

        return !$this->order ? 0 : $this->order;
    }


    /**
     * @param $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }


    /**
     * Get all properties of a field
     *
     * @return array
     */
    protected function loadProperties()
    {
        $this->property = ilDclCache::getFieldProperties($this->getId());
    }


    /**
     * Checks if a certain property for a field is set
     *
     * @param $key
     *
     * @return bool
     */
    public function hasProperty($key)
    {
        $this->loadProperties();

        return (isset($this->property[$key]) && $this->property[$key]->getValue() != null);
    }


    /**
     * Returns a certain property of a field
     *
     * @param $key
     *
     * @return null
     */
    public function getProperty($key)
    {
        $instance = $this->getPropertyInstance($key);

        return ($instance !== null) ? $instance->getValue() : null;
    }


    /**
     * Return ActiveRecord of property
     *
     * @param $key
     *
     * @return null
     */
    public function getPropertyInstance($key)
    {
        $this->loadProperties();
        if ($this->hasProperty($key)) {
            $value = $this->property[$key];

            return $value;
        }

        return null;
    }


    /**
     * Set a property for a field (does not save)
     *
     * @param $key
     * @param $value
     */
    public function setProperty($key, $value)
    {
        $this->loadProperties();
        if (isset($this->property[$key])) {
            $this->property[$key]->setValue($value);
        } else {
            $property = new ilDclFieldProperty();
            $property->setName($key);
            $property->setFieldId($this->getId());
            $property->setValue($value);

            $this->property[$key] = $property;
        }

        return $this->property[$key];
    }


    /**
     * Returns all valid properties for a field-type
     *
     * @return array
     */
    public function getValidFieldProperties()
    {
        return array();
    }


    /**
     * @param boolean $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }


    /**
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }


    /**
     * @param ilPropertyFormGUI $form
     * @param null              $record_id
     */
    public function checkValidityFromForm(ilPropertyFormGUI &$form, $record_id = null)
    {
        $value = $form->getInput('field_' . $this->getId());
        $this->checkValidity($value, $record_id);
    }


    /**
     * Check if input is valid
     *
     * @param      $value
     * @param null $record_id
     *
     * @return bool
     * @throws ilDclInputException
     */
    public function checkValidity($value, $record_id = null)
    {
        //Don't check empty values
        if ($value == null) {
            return true;
        }

        if ($this->isUnique()) {
            $table = ilDclCache::getTableCache($this->getTableId());
            foreach ($table->getRecords() as $record) {
                if ($this->normalizeValue($record->getRecordFieldValue($this->getId())) == $this->normalizeValue($value) && ($record->getId() != $record_id || $record_id == 0)) {
                    throw new ilDclInputException(ilDclInputException::UNIQUE_EXCEPTION);
                }
            }
        }

        return true;
    }


    /**
     * @param $value
     *
     * @return string
     */
    protected function normalizeValue($value)
    {
        if (is_string($value)) {
            $value = trim(preg_replace("/\\s+/uism", " ", $value));
        }

        return $value;
    }


    /**
     * @param $original_id
     *
     * @throws ilException
     */
    public function cloneStructure($original_id)
    {
        $original = ilDclCache::getFieldCache($original_id);
        $this->setTitle($original->getTitle());
        $this->setDatatypeId($original->getDatatypeId());
        $this->setDescription($original->getDescription());
        $this->setLocked($original->getLocked());
        $this->setOrder($original->getOrder());
        $this->setRequired($original->getRequired());
        $this->setUnique($original->isUnique());
        $this->setExportable($original->getExportable());
        $this->doCreate();
        $this->cloneProperties($original);

        // mandatory for all cloning functions
        ilDclCache::setCloneOf($original_id, $this->getId(), ilDclCache::TYPE_FIELD);
    }


    /**
     * @param $records
     */
    public function afterClone($records)
    {
        foreach ($records as $rec) {
            ilDclCache::getRecordFieldCache($rec, $this)->afterClone();
        }
    }


    /**
     * @param ilDclBaseFieldModel $originalField
     */
    public function cloneProperties(ilDclBaseFieldModel $originalField)
    {
        $orgProps = $originalField->getValidFieldProperties();
        if (count($orgProps) == 0) {
            return;
        }
        foreach ($orgProps as $prop_name) {
            $fieldprop_obj = new ilDclFieldProperty();
            $fieldprop_obj->setFieldId($this->getId());
            $fieldprop_obj->setName($prop_name);

            $value = $originalField->getProperty($prop_name);

            // If reference field, we must reset the referenced field, otherwise it will point to the old ID
            if ($originalField->getDatatypeId() == ilDclDatatype::INPUTFORMAT_REFERENCE && $prop_name == ilDclBaseFieldModel::PROP_REFERENCE) {
                $value = null;
            }

            $fieldprop_obj->setValue($value);
            $fieldprop_obj->create();
        }
    }


    /**
     * @param boolean $exportable
     */
    public function setExportable($exportable)
    {
        $this->exportable = $exportable;
    }


    /**
     * @return bool
     */
    public function allowFilterInListView()
    {
        return true;
    }


    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @param string  $direction
     * @param boolean $sort_by_status The specific sort object is a status field
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $sql_obj = new ilDclRecordQueryObject();

        $select_str = "sort_stloc_{$this->getId()}.value AS field_{$this->getId()}";
        $join_str
            = "LEFT JOIN il_dcl_record_field AS sort_record_field_{$this->getId()} ON (sort_record_field_{$this->getId()}.record_id = record.id AND sort_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS sort_stloc_{$this->getId()} ON (sort_stloc_{$this->getId()}.record_field_id = sort_record_field_{$this->getId()}.id)";

        $sql_obj->setSelectStatement($select_str);
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setOrderStatement("field_{$this->getId()} {$direction}");

        return $sql_obj;
    }


    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @param string                   $filter_value
     * @param ilDclBaseFieldModel|null $sort_field
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = null)
    {
        return null;
    }


    /**
     * Returns the sort-field id
     *
     * @return string
     */
    public function getSortField()
    {
        return $this->getTitle();
    }


    /**
     * Set to true, when the sorting should be handled numerical
     *
     * @return bool
     */
    public function hasNumericSorting()
    {
        if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_NUMBER) {
            return true;
        }

        return false;
    }


    /**
     * Checks input of specific fields befor saving
     *
     * @param ilPropertyFormGUI $form
     *
     * @return boolean if checkInput was successful
     */
    public function checkFieldCreationInput(ilPropertyFormGUI $form)
    {
        return true;
    }


    /**
     * @return int|null
     */
    public function getStorageLocationOverride()
    {
        return $this->storage_location_override;
    }


    /**
     * @param int|null $storage_location_override
     */
    public function setStorageLocationOverride($storage_location_override)
    {
        $this->storage_location_override = $storage_location_override;
    }


    /**
     * @param ilExcel $worksheet
     * @param         $row
     * @param         $col
     */
    public function fillHeaderExcel(ilExcel $worksheet, &$row, &$col)
    {
        $worksheet->setCell($row, $col, $this->getTitle());
        $col++;
    }


    /**
     * @param array $titles
     * @param array $import_fields
     */
    public function checkTitlesForImport(array &$titles, array &$import_fields)
    {
        foreach ($titles as $k => $title) {
            if (!ilStr::isUtf8($title)) {
                $title = utf8_encode($title);
            }
            if ($title == $this->getTitle()) {
                $import_fields[$k] = $this;
            }
        }
    }


    /**
     * called when saving the 'edit field' form
     *
     * @param ilPropertyFormGUI $form
     */
    public function storePropertiesFromForm(ilPropertyFormGUI $form)
    {
        $field_props = $this->getValidFieldProperties();
        foreach ($field_props as $property) {
            $representation = ilDclFieldFactory::getFieldRepresentationInstance($this);
            $value = $form->getInput($representation->getPropertyInputFieldId($property));

            // save non empty values and set them to null, when they already exist. Do not override plugin-hook when already set.
            if (!empty($value) || ($this->getPropertyInstance($property) != null && $property != self::PROP_PLUGIN_HOOK_NAME)) {
                $this->setProperty($property, $value)->store();
            }
        }
    }


    /**
     * called to fill the 'edit field' form
     *
     * @param ilPropertyFormGUI $form
     *
     * @return bool
     */
    public function fillPropertiesForm(ilPropertyFormGUI &$form)
    {
        $values = array(
            'table_id' => $this->getTableId(),
            'field_id' => $this->getId(),
            'title' => $this->getTitle(),
            'datatype' => $this->getDatatypeId(),
            'description' => $this->getDescription(),
            'required' => $this->getRequired(),
            'unique' => $this->isUnique(),
        );

        $properties = $this->getValidFieldProperties();
        foreach ($properties as $prop) {
            $values['prop_' . $prop] = $this->getProperty($prop);
        }

        $form->setValuesByArray($values);

        return true;
    }


    /**
     * called by ilDclFieldEditGUI when updating field properties
     * if you overwrite this method, remember to also overwrite getConfirmationGUI
     *
     * @param ilPropertyFormGUI $form
     *
     * @return bool
     */
    public function isConfirmationRequired(ilPropertyFormGUI $form)
    {
        return false;
    }


    /**
     * called by ilDclFieldEditGUI if isConfirmationRequired returns true
     *
     * @param ilPropertyFormGUI $form
     *
     * @return ilConfirmationGUI
     */
    public function getConfirmationGUI(ilPropertyFormGUI $form)
    {
        global $DIC;
        $ilConfirmationGUI = new ilConfirmationGUI();
        $ilConfirmationGUI->setFormAction($form->getFormAction());
        $ilConfirmationGUI->addHiddenItem('confirmed', 1);
        $ilConfirmationGUI->addHiddenItem('field_id', $form->getInput('field_id'));
        $ilConfirmationGUI->addHiddenItem('title', $form->getInput('title'));
        $ilConfirmationGUI->addHiddenItem('description', $form->getInput('description'));
        $ilConfirmationGUI->addHiddenItem('datatype', $form->getInput('datatype'));
        $ilConfirmationGUI->addHiddenItem('required', $form->getInput('required'));
        $ilConfirmationGUI->addHiddenItem('unique', $form->getInput('unique'));
        $ilConfirmationGUI->setConfirm($DIC->language()->txt('dcl_update_field'), 'update');
        $ilConfirmationGUI->setCancel($DIC->language()->txt('cancel'), 'edit');

        return $ilConfirmationGUI;
    }
}
