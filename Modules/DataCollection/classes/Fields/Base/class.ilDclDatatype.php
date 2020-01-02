<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclDatatype
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclDatatype
{
    const INPUTFORMAT_NONE = 0;
    const INPUTFORMAT_NUMBER = 1;
    const INPUTFORMAT_TEXT = 2;
    const INPUTFORMAT_REFERENCE = 3;
    const INPUTFORMAT_BOOLEAN = 4;
    const INPUTFORMAT_DATETIME = 5;
    const INPUTFORMAT_FILE = 6;
    const INPUTFORMAT_RATING = 7;
    const INPUTFORMAT_ILIAS_REF = 8;
    const INPUTFORMAT_MOB = 9;
    const INPUTFORMAT_REFERENCELIST = 10;
    const INPUTFORMAT_FORMULA = 11;
    const INPUTFORMAT_PLUGIN = 12;
    const INPUTFORMAT_NON_EDITABLE_VALUE = 13;
    const INPUTFORMAT_TEXT_SELECTION = 14;
    const INPUTFORMAT_DATE_SELECTION = 15;
    //public static $mob_suffixes = array('jpg', 'jpeg', 'gif', 'png', 'mp3', 'flx', 'mp4', 'm4v', 'mov', 'wmv');
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var int
     */
    protected $storageLocation;
    /**
     * @var string
     */
    protected $dbType;
    /**
     * @var ilDclDatatype[]
     */
    public static $datatype_cache;


    /**
     * Constructor
     *
     * @access public
     *
     * @param  integer datatype_id
     *
     */
    public function __construct($a_id = 0)
    {
        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }
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
     * Set title
     *
     * @param string $a_title
     */
    public function setTitle($a_title)
    {
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
     * Set Storage Location
     *
     * @param int $a_id
     */
    public function setStorageLocation($a_id)
    {
        $this->storageLocation = $a_id;
    }


    /**
     * Get Storage Location
     *
     * @return int
     */
    public function getStorageLocation()
    {
        return $this->storageLocation;
    }


    /*
     * getDbType
     */
    public function getDbType()
    {
        return $this->dbType;
    }


    /**
     * Read Datatype
     */
    public function doRead()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM il_dcl_datatype WHERE id = " . $ilDB->quote($this->getId(), "integer") . " ORDER BY sort";
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->loadDatatype($rec);
    }


    /**
     * Get all possible Datatypes
     *
     * @return array
     */
    public static function getAllDatatype()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (self::$datatype_cache == null) {
            self::$datatype_cache = array();

            $query = "SELECT * FROM il_dcl_datatype ORDER BY sort";
            $set = $ilDB->query($query);

            while ($rec = $ilDB->fetchAssoc($set)) {
                $instance = new ilDclDatatype();
                $instance->loadDatatype($rec);

                self::$datatype_cache[$rec['id']] = $instance;
            }
        }

        return self::$datatype_cache;
    }


    protected function loadDatatype($rec)
    {
        $this->id = $rec['id'];
        $this->dbType = $rec["ildb_type"];

        $this->setTitle($rec["title"]);
        $this->setStorageLocation($rec["storage_location"]);
    }
}
