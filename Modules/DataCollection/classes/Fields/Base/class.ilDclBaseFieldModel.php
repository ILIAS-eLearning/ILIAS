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
 ********************************************************************
 */

/**
 * Class ilDclBaseFieldModel
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclBaseFieldModel
{

    /**
     * @var int|string $id int for custom fields string for standard fields
     */
    protected $id = 0;
    protected int $table_id = 0;
    protected string $title = "";
    protected string $description = "";
    protected int $datatypeId = 0;
    protected ?int $order = null;
    protected bool $unique;
    /** @var ilDclFieldProperty[] */
    protected array $property = [];
    protected bool $exportable = false;
    protected ?ilDclDatatype $datatype = null;
    /**
     * With this property the datatype-storage-location can be overwritten. This need to be done in plugins.
     */
    protected ?int $storage_location_override = null;
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
    // type of table il_dcl_view
    const EDIT_VIEW = 2;
    const EXPORTABLE_VIEW = 4;

    public function __construct(int $a_id = 0)
    {
        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }
    }

    /**
     * All valid chars for filed titles
     */
    public static function _getTitleInvalidChars(bool $a_as_regex = true) : string
    {
        if ($a_as_regex) {
            return '/^[^<>\\\\":]*$/i';
        } else {
            return '\ < > " :';
        }
    }

    public static function _getFieldIdByTitle(string $title, int $table_id) : int
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
     * @param int|string
     */
    public function setId($a_id) : void
    {
        $this->id = $a_id;
    }

    /**
     * Get field id
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set table id
     */
    public function setTableId(int $a_id) : void
    {
        $this->table_id = $a_id;
    }

    /**
     * Get table id
     */
    public function getTableId() : int
    {
        return $this->table_id;
    }

    /**
     * Set title
     */
    public function setTitle(string $a_title) : void
    {
        //title cannot begin with _ as this is saved for other purposes. make __ instead.
        if (substr($a_title, 0, 1) == "_" && substr($a_title, 0, 2) != "__") {
            $a_title = "_" . $a_title;
        }
        $this->title = $a_title;
    }

    /**
     * Get title
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Set description
     */
    public function setDescription(string $a_desc) : void
    {
        $this->description = $a_desc;
    }

    /**
     * Get description
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Set datatype id
     */
    public function setDatatypeId(int $a_id) : void
    {
        //unset the cached datatype.
        $this->datatype = null;
        $this->datatypeId = $a_id;
    }

    /**
     * Get datatype_id
     */
    public function getDatatypeId() : int
    {
        if ($this->isStandardField()) {
            return ilDclStandardField::_getDatatypeForId($this->getId());
        }

        return $this->datatypeId;
    }

    public function isUnique() : bool
    {
        return $this->unique;
    }

    public function setUnique(?bool $unique) : void
    {
        $this->unique = $unique ? 1 : 0;
    }

    public function getDatatype() : ilDclDatatype
    {
        $this->loadDatatype();

        return $this->datatype;
    }

    public function getDatatypeTitle() : string
    {
        $this->loadDatatype();

        return $this->datatype->getTitle();
    }

    /**
     * Get storage location for the model
     */
    public function getStorageLocation() : ?int
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
    protected function loadDatatype() : void
    {
        if ($this->datatype == null) {
            $this->datatype = ilDclCache::getDatatype($this->datatypeId);
        }
    }

    /**
     * loadTableFieldSetting
     */
    protected function loadTableFieldSetting() : void
    {
        $tablefield_setting = ilDclTableFieldSetting::getInstance($this->getTableId(), $this->getId());
        $this->exportable = $tablefield_setting->isExportable();
        $this->order = $tablefield_setting->getFieldOrder();
    }

    /**
     * @return bool
     */
    public function getExportable() : bool
    {
        if (!isset($this->exportable)) {
            $this->loadExportability();
        }

        return $this->exportable;
    }

    /**
     * Load exportability
     */
    private function loadExportability() : void
    {
        if ($this->exportable == null) {
            $this->loadTableFieldSetting();
        }
    }

    public function toArray() : array
    {
        return (array) $this;
    }

    public function isStandardField() : bool
    {
        return false;
    }

    public function doRead() : void
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
        $this->setUnique($rec["is_unique"]);
        $this->loadProperties();
        $this->loadTableFieldSetting();
    }

    /**
     * Builds model from db record
     */
    public function buildFromDBRecord(array $rec) : void
    {
        $this->setId($rec["id"]);
        $this->setTableId($rec["table_id"]);
        $this->setTitle($rec["title"]);
        $this->setDescription($rec["description"]);
        $this->setDatatypeId($rec["datatype_id"]);
        $this->setUnique($rec["is_unique"] ?? null);
    }

    public function doCreate() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!ilDclTable::_tableExists($this->getTableId())) {
            throw new ilException("The field does not have a related table!");
        }

        $id = $ilDB->nextId("il_dcl_field");
        $this->setId($id);
        $query = "INSERT INTO il_dcl_field (" . "id" . ", table_id" . ", datatype_id" . ", title" . ", description" . ", is_unique"
            . " ) VALUES (" . $ilDB->quote($this->getId(), "integer") . "," . $ilDB->quote($this->getTableId(),
                "integer") . ","
            . $ilDB->quote($this->getDatatypeId(), "integer") . "," . $ilDB->quote($this->getTitle(), "text") . ","
            . $ilDB->quote($this->getDescription(), "text") . "," . $ilDB->quote($this->isUnique(), "integer") . ")";
        $ilDB->manipulate($query);

        $this->updateTableFieldSetting();

        $this->addToTableViews();
    }

    /**
     * create ilDclTableViewFieldSettings for this field in each tableview
     */
    protected function addToTableViews() : void
    {
        foreach (ilDclTableView::getAllForTableId($this->table_id) as $tableview) {
            $tableview->createFieldSetting($this->id);
        }
    }

    public function doUpdate() : void
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
                "is_unique" => array(
                    "integer",
                    $this->isUnique(),
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
    public function updateProperties() : void
    {
        foreach ($this->property as $prop) {
            $prop->store();
        }
    }

    /**
     * update exportable and fieldorder
     */
    protected function updateTableFieldSetting() : void
    {
        $tablefield_setting = ilDclTableFieldSetting::getInstance($this->getTableId(), $this->getId());
        $tablefield_setting->setExportable($this->exportable);
        $tablefield_setting->setFieldOrder($this->order);
        $tablefield_setting->store();
    }

    /**
     * Remove field and properties
     */
    public function doDelete() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // delete tablefield setting.
        ilDclTableFieldSetting::getInstance($this->getTableId(), $this->getId())->delete();

        $query = "DELETE FROM il_dcl_field_prop WHERE field_id = " . $ilDB->quote($this->getId(), "text");
        $ilDB->manipulate($query);

        $query = "DELETE FROM il_dcl_field WHERE id = " . $ilDB->quote($this->getId(), "text");
        $ilDB->manipulate($query);

        foreach ($this->getViewSettings() as $field_setting) {
            $field_setting->delete();
        }
    }

    /**
     * @return ilDclTableViewFieldSetting[]
     */
    public function getViewSettings() : array
    {
        return ilDclTableViewFieldSetting::where(array('field' => $this->getId()))->get();
    }

    public function getViewSetting(int $tableview_id) : ilDclTableViewFieldSetting
    {
        return ilDclTableViewFieldSetting::getTableViewFieldSetting($this->getId(), $tableview_id);
    }

    public function getOrder() : int
    {
        if ($this->order == null) {
            $this->loadTableFieldSetting();
        }

        return !$this->order ? 0 : $this->order;
    }

    public function setOrder(string $order) : void
    {
        $this->order = $order;
    }

    /**
     * Get all properties of a field
     */
    protected function loadProperties() : void
    {
        $this->property = ilDclCache::getFieldProperties($this->getId());
    }

    /**
     * Checks if a certain property for a field is set
     */
    public function hasProperty(string $key) : bool
    {
        $this->loadProperties();

        return (isset($this->property[$key]) && $this->property[$key]->getValue() != null);
    }

    /**
     * Returns a certain property of a field
     * @return ?mixed
     */
    public function getProperty(string $key)
    {
        $instance = $this->getPropertyInstance($key);

        return ($instance !== null) ? $instance->getValue() : null;
    }

    /**
     * Return ActiveRecord of property
     * @return ?ilDclFieldProperty
     */
    public function getPropertyInstance(string $key)
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
     * @param string|array|int $value
     */
    public function setProperty(string $key, $value) : ?ilDclFieldProperty
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
     */
    public function getValidFieldProperties() : array
    {
        return [];
    }

    public function checkValidityFromForm(ilPropertyFormGUI &$form, ?int $record_id = null) : void
    {
        $value = $form->getInput('field_' . $this->getId());
        $this->checkValidity($value, $record_id);
    }

    /**
     * Check if input is valid
     * @param float|int|string|array|null $value
     * @throws ilDclInputException
     */
    public function checkValidity($value, ?int $record_id = null) : bool
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
     * @param mixed $value
     */
    protected function normalizeValue($value) : string
    {
        if (is_string($value)) {
            $value = trim(preg_replace("/\\s+/uism", " ", $value));
        }

        return $value;
    }

    /**
     * @throws ilException
     */
    public function cloneStructure(int $original_id) : void
    {
        $original = ilDclCache::getFieldCache($original_id);
        $this->setTitle($original->getTitle());
        $this->setDatatypeId($original->getDatatypeId());
        $this->setDescription($original->getDescription());
        $this->setOrder($original->getOrder());
        $this->setUnique($original->isUnique());
        $this->setExportable($original->getExportable());
        $this->doCreate();
        $this->cloneProperties($original);

        // mandatory for all cloning functions
        ilDclCache::setCloneOf($original_id, $this->getId(), ilDclCache::TYPE_FIELD);
    }

    public function afterClone(array $records)
    {
        foreach ($records as $rec) {
            ilDclCache::getRecordFieldCache($rec, $this)->afterClone();
        }
    }

    public function cloneProperties(ilDclBaseFieldModel $originalField) : void
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

    public function setExportable(bool $exportable) : void
    {
        $this->exportable = $exportable;
    }

    public function allowFilterInListView() : bool
    {
        return true;
    }

    /**
     * Returns a query-object for building the record-loader-sql-query
     * @param bool $sort_by_status The specific sort object is a status field
     */
    public function getRecordQuerySortObject(
        string $direction = "asc",
        bool $sort_by_status = false
    ) : ?ilDclRecordQueryObject {
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
     * @param string|int $filter_value
     */
    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ) : ?ilDclRecordQueryObject {
        return null;
    }

    /**
     * Returns the sort-field id
     */
    public function getSortField() : string
    {
        return $this->getTitle();
    }

    /**
     * Set to true, when the sorting should be handled numerical
     */
    public function hasNumericSorting() : bool
    {
        if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_NUMBER) {
            return true;
        }

        return false;
    }

    /**
     * Checks input of specific fields befor saving
     * @param ilPropertyFormGUI $form
     * @return bool if checkInput was successful
     */
    public function checkFieldCreationInput(ilPropertyFormGUI $form) : bool
    {
        return true;
    }

    public function getStorageLocationOverride() : ?int
    {
        return $this->storage_location_override;
    }

    public function setStorageLocationOverride(?int $storage_location_override) : void
    {
        $this->storage_location_override = $storage_location_override;
    }

    public function fillHeaderExcel(ilExcel $worksheet, int &$row, int &$col) : void
    {
        $worksheet->setCell($row, $col, $this->getTitle());
        $col++;
    }

    public function checkTitlesForImport(array &$titles, array &$import_fields) : void
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
     */
    public function storePropertiesFromForm(ilPropertyFormGUI $form) : void
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
     */
    public function fillPropertiesForm(ilPropertyFormGUI &$form) : bool
    {
        $values = array(
            'table_id' => $this->getTableId(),
            'field_id' => $this->getId(),
            'title' => $this->getTitle(),
            'datatype' => $this->getDatatypeId(),
            'description' => $this->getDescription(),
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
     */
    public function isConfirmationRequired(ilPropertyFormGUI $form) : bool
    {
        return false;
    }

    /**
     * called by ilDclFieldEditGUI if isConfirmationRequired returns true
     */
    public function getConfirmationGUI(ilPropertyFormGUI $form) : ilConfirmationGUI
    {
        global $DIC;
        $ilConfirmationGUI = new ilConfirmationGUI();
        $ilConfirmationGUI->setFormAction($form->getFormAction());
        $ilConfirmationGUI->addHiddenItem('confirmed', 1);
        $ilConfirmationGUI->addHiddenItem('field_id', $form->getInput('field_id'));
        $ilConfirmationGUI->addHiddenItem('title', $form->getInput('title'));
        $ilConfirmationGUI->addHiddenItem('description', $form->getInput('description'));
        $ilConfirmationGUI->addHiddenItem('datatype', $form->getInput('datatype'));
        $ilConfirmationGUI->addHiddenItem('unique', $form->getInput('unique'));
        $ilConfirmationGUI->setConfirm($DIC->language()->txt('dcl_update_field'), 'update');
        $ilConfirmationGUI->setCancel($DIC->language()->txt('cancel'), 'edit');

        return $ilConfirmationGUI;
    }
}
