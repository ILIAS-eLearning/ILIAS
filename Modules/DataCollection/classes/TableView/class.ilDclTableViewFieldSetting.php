<?php

/**
 * Class ilDclTableViewFieldSetting
 * defines tableview/field specific settings: visible, in_filter, filter_value, filter_changeable
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewFieldSetting extends ActiveRecord
{

    /**
     * @var int
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_sequence         true
     */
    protected ?int $id;
    /**
     * @var int
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $tableview_id;
    /**
     * @var string
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected string $field = "";
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $visible = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $in_filter = false;
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $filter_value = "";
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $filter_changeable = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $required_create = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $locked_create = false;
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected ?string $default_value = null;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_is_notnull       true
     * @db_length           1
     */
    protected bool $visible_create = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_is_notnull       true
     * @db_length           1
     */
    protected bool $visible_edit = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $required_edit = false;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $locked_edit = false;

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName() : string
    {
        return "il_dcl_tview_set";
    }

    public function getTableviewId() : int
    {
        return $this->tableview_id;
    }

    public function setTableviewId(int $tableview_id) : void
    {
        $this->tableview_id = $tableview_id;
    }

    public function getField() : string
    {
        return $this->field;
    }

    /**
     * @param $field
     */
    public function setField(string $field) : void
    {
        $this->field = $field;
    }

    public function isVisibleInList() : bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible) : void
    {
        $this->visible = $visible;
    }

    public function isInFilter() : bool
    {
        return $this->in_filter;
    }

    public function setInFilter(bool $in_filter) : void
    {
        $this->in_filter = $in_filter;
    }

    public function getFilterValue()
    {
        return $this->filter_value;
    }

    public function setFilterValue($filter_value) : void
    {
        $this->filter_value = $filter_value;
    }

    public function isFilterChangeable() : bool
    {
        return $this->filter_changeable;
    }

    public function setFilterChangeable(bool $filter_changeable) : void
    {
        $this->filter_changeable = $filter_changeable;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function isRequiredCreate() : bool
    {
        return $this->required_create;
    }

    public function setRequiredCreate(bool $required_create) : void
    {
        $this->required_create = $required_create;
    }

    public function isLockedCreate() : bool
    {
        return $this->locked_create;
    }

    public function setLockedCreate(bool $locked_create) : void
    {
        $this->locked_create = $locked_create;
    }

    public function isRequiredEdit() : bool
    {
        return $this->required_edit;
    }

    public function setRequiredEdit(bool $required_edit) : void
    {
        $this->required_edit = $required_edit;
    }

    public function isLockedEdit() : bool
    {
        return $this->locked_edit;
    }

    public function setLockedEdit(bool $locked_edit) : void
    {
        $this->locked_edit = $locked_edit;
    }

    public function getDefaultValue() : ?string
    {
        return $this->default_value;
    }

    public function setDefaultValue(?string $default_value) : void
    {
        $this->default_value = $default_value;
    }

    public function isVisibleCreate() : bool
    {
        return $this->visible_create;
    }

    public function setVisibleCreate(bool $visible_create) : void
    {
        $this->visible_create = $visible_create;
    }

    public function setNotVisibleCreate(bool $not_visible_create) : void
    {
        $this->visible_create = !$not_visible_create;
    }

    public function isNotVisibleCreate() : bool
    {
        return !$this->visible_create;
    }

    public function isVisibleEdit() : bool
    {
        return $this->visible_edit;
    }

    public function setVisibleEdit(bool $visible_edit) : void
    {
        $this->visible_edit = $visible_edit;
    }

    public function setNotVisibleEdit(bool $not_visible) : void
    {
        $this->visible_edit = !$not_visible;
    }

    public function isNotVisibleEdit() : bool
    {
        return !$this->visible_edit;
    }

    public function isVisibleInForm(bool $creation_mode) : bool
    {
        return $creation_mode ? $this->isVisibleCreate() : $this->isVisibleEdit();
    }

    public function isLocked(bool $creation_mode) : bool
    {
        return $creation_mode ? $this->isLockedCreate() : $this->isLockedEdit();
    }

    public function isRequired(bool $creation_mode) : bool
    {
        return $creation_mode ? $this->isRequiredCreate() : $this->isRequiredEdit();
    }

    /**
     * @param $field_name
     * @return null|string
     */
    public function sleep($field_name)
    {
        if ($field_name == 'filter_value' && is_array($this->filter_value)) {
            return json_encode($this->filter_value);
        }

        return null;
    }

    public function wakeUp($field_name, $field_value) : ?array
    {
        if ($field_name == 'filter_value') {
            $return = array();
            $json = json_decode($field_value, true);
            if (is_array($json)) {
                foreach ($json as $key => $value) {
                    $return['filter_' . $this->getField() . '_' . $key] = $value;
                }
            } else {
                $return = array('filter_' . $this->getField() => $field_value);
            }

            return $return;
        }

        return null;
    }

    public function cloneStructure(ilDclTableViewFieldSetting $orig) : int
    {
        $this->setFilterChangeable($orig->isFilterChangeable());
        $this->setInFilter($orig->isInFilter());
        $this->setVisibleCreate($orig->isVisibleCreate());
        $this->setVisibleEdit($orig->isVisibleEdit());
        $this->setLockedCreate($orig->isLockedCreate());
        $this->setLockedEdit($orig->isLockedEdit());
        $this->setRequiredCreate($orig->isRequiredCreate());
        $this->setRequiredEdit($orig->isRequiredEdit());
        $this->setFilterValue($orig->getFilterValue());
        $this->create();
        return $this->getId();
    }

    /**
     * @return ilDclBaseFieldModel|ilDclStandardField
     */
    public function getFieldObject()
    {
        if (is_numeric($this->field)) {   //normal field
            return ilDclCache::getFieldCache($this->field);
        } else {   //standard field
            global $DIC;
            $lng = $DIC['lng'];
            $stdfield = new ilDclStandardField();
            $stdfield->setId($this->field);
            $stdfield->setDatatypeId(ilDclStandardField::_getDatatypeForId($this->field));
            $stdfield->setTitle($lng->txt('dcl_' . $this->field));

            return $stdfield;
        }
    }

    /**
     * @return ActiveRecord|self
     */
    public static function getTableViewFieldSetting(int $id, int $tableview_id) : ActiveRecord
    {
        return parent::where(array('field' => $id,
                                   'tableview_id' => $tableview_id
        ))->first();
    }

    /**
     * @param $tableview_id
     * @param $field_id
     * @return ActiveRecord|self
     */
    public static function getInstance(int $tableview_id, int $field_id) : ActiveRecord
    {
        if (!($setting = self::where(array('field' => $field_id, 'tableview_id' => $tableview_id))->first())) {
            $setting = new self();
            $setting->setField($field_id);
            $setting->setTableviewId($tableview_id);

        }
        return $setting;
    }

}
