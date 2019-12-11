<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Media Pool Item
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolItem
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $import_id;

    /**
     * Construtor
     *
     * @param	int		media pool item id
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }
    
    /**
     * Set id
     *
     * @param	int	id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
     * Get id
     *
     * @return	int	id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param	string	type
     */
    public function setType($a_val)
    {
        $this->type = $a_val;
    }
    
    /**
     * Get type
     *
     * @return	string	type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set foreign id
     *
     * @param	int	foreign id
     */
    public function setForeignId($a_val)
    {
        $this->foreign_id = $a_val;
    }
    
    /**
     * Get foreign id
     *
     * @return	int	foreign id
     */
    public function getForeignId()
    {
        return $this->foreign_id;
    }

    /**
     * Set import id
     *
     * @param string $a_val import id
     */
    public function setImportId($a_val)
    {
        $this->import_id = $a_val;
    }
    
    /**
     * Get import id
     *
     * @return string import id
     */
    public function getImportId()
    {
        return $this->import_id;
    }
    
    /**
     * Set title
     *
     * @param	string	title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }
    
    /**
     * Get title
     *
     * @return	string	title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Create
     */
    public function create()
    {
        $ilDB = $this->db;
        
        $nid = $ilDB->nextId("mep_item");
        $ilDB->manipulate("INSERT INTO mep_item " .
            "(obj_id, type, foreign_id, title, import_id) VALUES (" .
            $ilDB->quote($nid, "integer") . "," .
            $ilDB->quote($this->getType(), "text") . "," .
            $ilDB->quote($this->getForeignId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getImportId(), "text") .
            ")");
        $this->setId($nid);
    }
    
    /**
     * Read
     */
    public function read()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM mep_item WHERE " .
            "obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
        if ($rec  = $ilDB->fetchAssoc($set)) {
            $this->setType($rec["type"]);
            $this->setForeignId($rec["foreign_id"]);
            $this->setTitle($rec["title"]);
            $this->setImportId($rec["import_id"]);
        }
    }
    
    /**
     * Update
     *
     * @param
     * @return
     */
    public function update()
    {
        $ilDB = $this->db;
    
        $ilDB->manipulate(
            "UPDATE mep_item SET " .
            " type = " . $ilDB->quote($this->getType(), "text") . "," .
            " foreign_id = " . $ilDB->quote($this->getForeignId(), "integer") . "," .
            " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            " import_id = " . $ilDB->quote($this->getImportId(), "text") .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }
    
    /**
     * Delete
     *
     * @param
     * @return
     */
    public function delete()
    {
        $ilDB = $this->db;
    
        $ilDB->manipulate(
            "DELETE FROM mep_item WHERE "
            . " obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }
    
    /**
     * Lookup
     *
     * @param
     * @return
     */
    private static function lookup($a_id, $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT " . $a_field . " FROM mep_item WHERE " .
            " obj_id = " . $ilDB->quote($a_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec[$a_field];
        }
        return false;
    }
    
    /**
     * Lookup Foreign Id
     *
     * @param	int		mep item id
     */
    public static function lookupForeignId($a_id)
    {
        return self::lookup($a_id, "foreign_id");
    }

    /**
     * Lookup type
     *
     * @param	int		mep item id
     */
    public static function lookupType($a_id)
    {
        return self::lookup($a_id, "type");
    }

    /**
     * Lookup title
     *
     * @param	int		mep item id
     */
    public static function lookupTitle($a_id)
    {
        return self::lookup($a_id, "title");
    }
    
    /**
     * Update object title
     *
     * @param
     * @return
     */
    public static function updateObjectTitle($a_obj)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (ilObject::_lookupType($a_obj) == "mob") {
            $title = ilObject::_lookupTitle($a_obj);
            $ilDB->manipulate(
                "UPDATE mep_item SET " .
                " title = " . $ilDB->quote($title, "text") .
                " WHERE foreign_id = " . $ilDB->quote($a_obj, "integer") .
                " AND type = " . $ilDB->quote("mob", "text")
            );
        }
    }
    
    /**
     * Get media pools for item id
     */
    public static function getPoolForItemId($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM mep_tree " .
            " WHERE child = " . $ilDB->quote($a_id, "integer")
        );
        $pool_ids = array();
        while ($rec  = $ilDB->fetchAssoc($set)) {
            $pool_ids[] = $rec["mep_id"];
        }
        return $pool_ids;		// currently this array should contain only one id
    }

    /**
     * Get all ids for type
     *
     * @param
     * @return
     */
    public static function getIdsForType($a_id, $a_type)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT mep_tree.child as id" .
            " FROM mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) WHERE " .
            " mep_tree.mep_id = " . $ilDB->quote($a_id, "integer") . " AND " .
            " mep_item.type = " . $ilDB->quote($a_type, "text")
        );

        $ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = $rec["id"];
        }
        return $ids;
    }
}
