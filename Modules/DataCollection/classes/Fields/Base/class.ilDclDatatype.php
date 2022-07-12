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
 * Class ilDclDatatype
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
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
    const INPUTFORMAT_TEXT_SELECTION = 14;
    const INPUTFORMAT_DATE_SELECTION = 15;

    protected int $id = 0;
    protected string $title = "";
    protected int $storageLocation = 0;
    protected string $dbType;
    /**
     * @var ilDclDatatype[]
     */
    public static array $datatype_cache = [];

    /**
     * Constructor
     * @access public
     */
    public function __construct(int $a_id = 0)
    {
        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Set Storage Location
     */
    public function setStorageLocation(int $a_id) : void
    {
        $this->storageLocation = $a_id;
    }

    /**
     * Get Storage Location
     */
    public function getStorageLocation() : int
    {
        return $this->storageLocation;
    }

    public function getDbType() : string
    {
        return $this->dbType;
    }

    /**
     * Read Datatype
     */
    public function doRead() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM il_dcl_datatype WHERE id = " . $ilDB->quote($this->getId(),
                "integer") . " ORDER BY sort";
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->loadDatatype($rec);
    }

    /**
     * Get all possible Datatypes
     */
    public static function getAllDatatype() : array
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

    protected function loadDatatype(array $rec) : void
    {
        $this->id = $rec['id'];
        $this->dbType = $rec["ildb_type"];

        $this->setTitle($rec["title"]);
        $this->setStorageLocation($rec["storage_location"]);
    }
}
