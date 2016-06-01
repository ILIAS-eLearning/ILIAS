<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');
require_once('./Modules/DataCollection/classes/TableView/class.ilDclTableViewFieldSetting.php');
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
        foreach (ilDclTableViewFieldSetting::getAllForTableViewId($this->id) as $setting)
        {
            $setting->delete();
        }
        parent::delete();
    }
    
    public function getTable()
    {
        return ilDclCache::getTableCache($this->table_id);
    }

    /**
     * getFilterableFields
     * Returns all  field-objects (or field-settings if flag is true) of this tableview which have set their filterable to true, including standard fields.
     *
     * @return ilDclBaseFieldModel[]|ilDclTableViewFieldSetting[]
     */
    public function getFilterableFields($as_field_setting = false)
    {
        return $this->getFields('in_filter', $as_field_setting);
    }

    /**
     * Returns all field-objects (or field-settings if flag is true) of this tableview which have set their visibility to true, including standard fields.
     *
     * @return ilDclBaseFieldModel[]|ilDclTableViewFieldSetting[]
     */
    public function getVisibleFields($as_field_setting = false) {
        return $this->getFields('visible', $as_field_setting);
    }

    /**
     * @param $property
     * @param bool $as_field_setting
     * @return array
     */
    public function getFields($property, $as_field_setting = false)
    {
        $fieldRecords = ilDclTableViewFieldSetting::where(array("tableview_id" => $this->id, $property => true))->get();
        if ($as_field_setting)
        {
            return $fieldRecords;
        }

        $fields = array();

        foreach ($fieldRecords as $field_rec) {
            $fields[] = $field_rec->getFieldObject();
        }
        return $fields;
    }

    public function create($create_default_settings = true)
    {
        parent::create();
        if ($create_default_settings)
        {
            $this->createDefaultSettings();
        }
    }


    /**
     * create default ilDclTableViewFieldSetting entries
     */
    public function createDefaultSettings()
    {
        $table = new ilDclTable($this->table_id);

        foreach ($table->getFieldIds() as $field_id)
        {
            $this->addField($field_id);
        }
    }

    /**
     * create ilDclTableViewFieldSetting for this tableview and the given field id
     *
     * @param $field_id
     */
    public function addField($field_id)
    {
        if (!ilDclTableViewFieldSetting::where(
            array('tableview_id' => $this->id, 'field' => $field_id))->get())
        {
            $field_set = new ilDclTableViewFieldSetting();
            $field_set->setTableviewId($this->id);
            $field_set->setField($field_id);
            $field_set->setVisible(!ilDclStandardField::_isStandardField($field_id));
            $field_set->create();
        }
    }


    /**
     * @param $table_id
     * @return ilDclTableView[]
     */
    public static function getAllForTableId($table_id)
    {
        return self::where(array('table_id' => $table_id))->orderBy('tableview_order')->get();
    }

    /**
     * @param $table_id
     * @return int
     */
    public static function getCountForTableId($table_id)
    {
        return self::where(array('table_id' => $table_id))->orderBy('tableview_order')->count();
    }

    /**
     * @param $table_id
     * @param bool $create_default_settings
     * @return ilDclTableView
     */
    public static function createStandardView($table_id, $create_default_settings = true)
    {
        global $lng, $rbacreview;
        $view = new self();
        $view->setRoles(array_merge($rbacreview->getGlobalRoles(), $rbacreview->getLocalRoles($_GET['ref_id'])));
        $view->setTableId($table_id);
        $view->setTitle($lng->txt('dcl_standardview'));
        $view->setTableviewOrder(10);
        $view->create($create_default_settings);
        return $view;
    }

}