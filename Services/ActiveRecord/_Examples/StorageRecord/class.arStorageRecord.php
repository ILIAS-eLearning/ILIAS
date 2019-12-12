<?php
require_once('./Services/ActiveRecord/_Examples/StorageRecord/class.arStorageRecordStorage.php');
require_once('./Services/ActiveRecord/Storage/int.arStorageInterface.php');

/**
 * Class arTestRecord
 *
 * @description A Class which does not extend from ActiveRecord
 *              uses arStorage for dynamic DB usage
 *
 * @author      Fabian Schmid <fs@studer-raimann.ch>
 * @version     2.0.7
 */
class arStorageRecord implements arStorageInterface
{

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           4
     */
    protected $id = 0;
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           200
     */
    protected $title = '';
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           200
     */
    public $description = '';
    /**
     * @var array
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           200
     */
    protected $usr_ids = array();
    /**
     * @var arStorageRecordStorage
     */
    protected $storage;


    /**
     * @param $id
     */
    public function __construct($id = 0)
    {
        $this->id = $id;
        $this->storage = arStorageRecordStorage::getInstance($this);
        $this->storage->installDB();
    }


    public function create()
    {
        $this->storage->create();
    }


    public function update()
    {
        $this->storage->update();
    }


    public function delete()
    {
        $this->storage->delete();
    }


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param array $usr_ids
     */
    public function setUsrIds($usr_ids)
    {
        $this->usr_ids = $usr_ids;
    }


    /**
     * @return array
     */
    public function getUsrIds()
    {
        return $this->usr_ids;
    }


    /**
     * @param arStorageRecordStorage $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }


    /**
     * @return arStorageRecordStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
