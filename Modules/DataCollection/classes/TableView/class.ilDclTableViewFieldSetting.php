<?php
require_once('.Services/ActiveRecord/class.ActiveRecord.php');
/**
 * Class ilDclTableViewFieldSetting
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTableViewFieldSetting extends ActiveRecord
{
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
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     *
     */
    protected $field_id;

    /**
     * @var boolean
     *
     * @db_has_field        true
     * @db_fieldtype        boolean
     * @db_length           1
     */
    protected $visible;

    /**
     * @var boolean
     *
     * @db_has_field        true
     * @db_fieldtype        boolean
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
     * @var boolean
     *
     * @db_has_field        true
     * @db_fieldtype        boolean
     * @db_length           1
     */
    protected $filter_changeable;

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName() {
        return "il_dcl_tableview_field_setting";
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
    public function getFieldId()
    {
        return $this->field_id;
    }

    /**
     * @param int $field_id
     */
    public function setFieldId($field_id)
    {
        $this->field_id = $field_id;
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
    
}