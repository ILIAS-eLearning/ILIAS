<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');
/**
 * Class ilDclTableViewFieldSetting
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
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName() {
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
    public function isVisible()
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
     * @param $field_name
     * @return null|string
     */
    public function sleep($field_name)
    {
        if ($field_name == 'filter_value' && is_array($this->filter_value))
        {
            return json_encode($this->filter_value);
        }
        return null;
    }

    /**
     * @param $field_name
     * @param $field_value
     * @return mixed|null
     */
    public function wakeUp($field_name, $field_value)
    {
        if ($field_name == 'filter_value')
        {
            $json = json_decode($field_value, true);
            if (is_array($json))
            {
                return $json;
            }
        }
        return null;
    }

    public static function createDefaults($table_id, $tableview_id)
    {
        $table = new ilDclTable($table_id);
        
        foreach ($table->getFieldIds() as $field_id)
        {
            $setting = new self();
            $setting->setTableviewId($tableview_id);
            $setting->setField($field_id);
            $setting->setVisible(!ilDclStandardField::_isStandardField($field_id));
            $setting->create();
        }
    }

}