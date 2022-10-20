<?php

/**
 * Class ilDclTableViewFieldSetting
 *
 * defines tableview/field specific settings: visible, in_filter, filter_value, filter_changeable
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewFieldSetting extends ActiveRecord
{

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_sequence         true
     */
    protected $id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $tableview_id;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     *
     */
    protected $field;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $visible;
    /**
     * @var boolean
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $in_filter;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $filter_value;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $filter_changeable;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $required_create;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $locked_create;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $default_value;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_is_notnull       true
     * @db_length           1
     */
    protected $visible_create;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_is_notnull       true
     * @db_length           1
     */
    protected $visible_edit;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $required_edit;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $locked_edit;


    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return "il_dcl_tview_set";
    }


    /**
     * @return int
     */
    public function getTableviewId()
    {
        return $this->tableview_id;
    }


    /**
     * @param int $tableview_id
     */
    public function setTableviewId($tableview_id)
    {
        $this->tableview_id = $tableview_id;
    }


    /**
     * @return int
     */
    public function getField()
    {
        return $this->field;
    }


    /**
     * @param int $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }


    /**
     * @return boolean
     */
    public function isVisibleInList()
    {
        return $this->visible;
    }


    /**
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }


    /**
     * @return boolean
     */
    public function isInFilter()
    {
        return $this->in_filter;
    }


    /**
     * @param boolean $in_filter
     */
    public function setInFilter($in_filter)
    {
        $this->in_filter = $in_filter;
    }


    /**
     * @return string
     */
    public function getFilterValue()
    {
        return $this->filter_value;
    }


    /**
     * @param string $filter_value
     */
    public function setFilterValue($filter_value)
    {
        $this->filter_value = $filter_value;
    }


    /**
     * @return boolean
     */
    public function isFilterChangeable()
    {
        return $this->filter_changeable;
    }


    /**
     * @param boolean $filter_changeable
     */
    public function setFilterChangeable($filter_changeable)
    {
        $this->filter_changeable = $filter_changeable;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return bool
     */
    public function isRequiredCreate()
    {
        return $this->required_create;
    }


    /**
     * @param bool $required_create
     */
    public function setRequiredCreate($required_create)
    {
        $this->required_create = $required_create;
    }


    /**
     * @return bool
     */
    public function isLockedCreate()
    {
        return $this->locked_create;
    }


    /**
     * @param bool $locked_create
     */
    public function setLockedCreate($locked_create)
    {
        $this->locked_create = $locked_create;
    }


    /**
     * @return bool
     */
    public function isRequiredEdit()
    {
        return $this->required_edit;
    }


    /**
     * @param bool $required_edit
     */
    public function setRequiredEdit($required_edit)
    {
        $this->required_edit = $required_edit;
    }


    /**
     * @return bool
     */
    public function isLockedEdit()
    {
        return $this->locked_edit;
    }


    /**
     * @param bool $locked_edit
     */
    public function setLockedEdit($locked_edit)
    {
        $this->locked_edit = $locked_edit;
    }


    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }


    /**
     * @param string $default_value
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;
    }


    /**
     * @return bool
     */
    public function isVisibleCreate()
    {
        return $this->visible_create;
    }


    /**
     * @param bool $visible_create
     */
    public function setVisibleCreate($visible_create)
    {
        $this->visible_create = $visible_create;
    }


    /**
     * @param bool $not_visible_create
     */
    public function setNotVisibleCreate(bool $not_visible_create) : void
    {
        $this->visible_create = !$not_visible_create;
    }


    /**
     * @return bool
     */
    public function isNotVisibleCreate() : bool
    {
        return !$this->visible_create;
    }


    /**
     * @return bool
     */
    public function isVisibleEdit()
    {
        return $this->visible_edit;
    }


    /**
     * @param bool $visible_edit
     */
    public function setVisibleEdit($visible_edit)
    {
        $this->visible_edit = $visible_edit;
    }

    /**
     * @param bool $not_visible
     */
    public function setNotVisibleEdit(bool $not_visible) : void
    {
        $this->visible_edit = !$not_visible;
    }

    /**
     * @return bool
     */
    public function isNotVisibleEdit() : bool
    {
        return !$this->visible_edit;
    }

    /**
     * @param bool $creation_mode
     * @return bool
     */
    public function isVisibleInForm(bool $creation_mode) : bool
    {
        return $creation_mode ? $this->isVisibleCreate() : $this->isVisibleEdit();
    }

    /**
     * @param bool $creation_mode
     * @return bool
     */
    public function isLocked(bool $creation_mode) : bool
    {
        return $creation_mode ? $this->isLockedCreate() : $this->isLockedEdit();
    }

    /**
     * @param bool $creation_mode
     * @return bool
     */
    public function isRequired(bool $creation_mode) : bool
    {
        return $creation_mode ? $this->isRequiredCreate() : $this->isRequiredEdit();
    }

    /**
     * @param $field_name
     *
     * @return null|string
     */
    public function sleep($field_name)
    {
        if ($field_name == 'filter_value' && is_array($this->filter_value)) {
            return json_encode($this->filter_value);
        }

        return null;
    }


    /**
     * @param $field_name
     * @param $field_value
     *
     * @return mixed|null
     */
    public function wakeUp($field_name, $field_value)
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


    public function cloneStructure(ilDclTableViewFieldSetting $orig)
    {
        $this->setFilterChangeable($orig->isFilterChangeable());
        $this->setInFilter($orig->isInFilter());
        $this->setVisible($orig->isVisibleInList());
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
     * @param $tableview_id
     * @param $field_id
     *
     * @return ActiveRecord
     */
    public static function getInstance($tableview_id, $field_id)
    {
        if ($setting = self::where(array('field' => $field_id, 'tableview_id' => $tableview_id))->first()) {
            return $setting;
        } else {
            $setting = new self();
            $setting->setField($field_id);
            $setting->setTableviewId($tableview_id);

            return $setting;
        }
    }
}
