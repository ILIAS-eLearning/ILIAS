<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');
/**
 * Class ilDclTableView
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableView extends ActiveRecord
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
     *
     */
    protected $table_id;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $title;

    /**
     * @var array
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected $roles;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $description;

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $tableview_order;

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName() {
        return "il_dcl_tableview";
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
     * @return int
     */
    public function getTableId()
    {
        return $this->table_id;
    }

    /**
     * @param int $table_id
     */
    public function setTableId($table_id)
    {
        $this->table_id = $table_id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->tableview_order;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->tableview_order = $order;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getTableviewOrder()
    {
        return $this->tableview_order;
    }

    /**
     * @param int $tableview_order
     */
    public function setTableviewOrder($tableview_order)
    {
        $this->tableview_order = $tableview_order;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param $field_name
     * @return null|string
     */
    public function sleep($field_name)
    {
        if ($field_name == 'roles')
        {
            return json_encode($this->roles);
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
        if ($field_name == 'roles')
        {
            return json_decode($field_value);
        }
        return null;
    }

    public function delete()
    {
        foreach (ilDclTableViewFieldSetting::where(array('tableview_id' => $this->id))->get() as $setting)
        {
            $setting->delete();
        }
        parent::delete();
    }
    

}