<?php
require_once('.Services/ActiveRecord/class.ActiveRecord.php');
/**
 * Class ilDclTableViewAccess
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTableViewAccess extends ActiveRecord
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
     */
    protected $role_id;

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName() {
        return "il_dcl_tableview_access";
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
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * @param int $role_id
     */
    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;
    }



}