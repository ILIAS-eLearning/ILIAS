<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* This is the super class of all custom blocks.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilCustomBlock
{
    /**
     * @var ilDB
     */
    protected $db;


    protected $id;
    protected $context_obj_id;
    protected $context_obj_type;
    protected $context_sub_obj_id;
    protected $context_sub_obj_type;
    protected $type;
    protected $title;

    /**
    * Constructor.
    *
    * @param	int	$a_id
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
    * Set Id.
    *
    * @param	int	$a_id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
    * Get Id.
    *
    * @return	int
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Set ContextObjId.
    *
    * @param	int	$a_context_obj_id
    */
    public function setContextObjId($a_context_obj_id)
    {
        $this->context_obj_id = $a_context_obj_id;
    }

    /**
    * Get ContextObjId.
    *
    * @return	int
    */
    public function getContextObjId()
    {
        return (int) $this->context_obj_id;
    }

    /**
    * Set ContextObjType.
    *
    * @param	int	$a_context_obj_type
    */
    public function setContextObjType($a_context_obj_type)
    {
        $this->context_obj_type = $a_context_obj_type;
    }

    /**
    * Get ContextObjType.
    *
    * @return	int
    */
    public function getContextObjType()
    {
        return $this->context_obj_type;
    }

    /**
    * Set ContextSubObjId.
    *
    * @param	int	$a_context_sub_obj_id
    */
    public function setContextSubObjId($a_context_sub_obj_id)
    {
        $this->context_sub_obj_id = $a_context_sub_obj_id;
    }

    /**
    * Get ContextSubObjId.
    *
    * @return	int
    */
    public function getContextSubObjId()
    {
        return (int) $this->context_sub_obj_id;
    }

    /**
    * Set ContextSubObjType.
    *
    * @param	int	$a_context_sub_obj_type
    */
    public function setContextSubObjType($a_context_sub_obj_type)
    {
        $this->context_sub_obj_type = $a_context_sub_obj_type;
    }

    /**
    * Get ContextSubObjType.
    *
    * @return	int
    */
    public function getContextSubObjType()
    {
        return $this->context_sub_obj_type;
    }

    /**
    * Set Type.
    *
    * @param	string	$a_type	Type of block.
    */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
    * Get Type.
    *
    * @return	string	Type of block.
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * Set Title.
    *
    * @param	string	$a_title	Title of block
    */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
    * Get Title.
    *
    * @return	string	Title of block
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * Create new item.
    *
    */
    public function create()
    {
        $ilDB = $this->db;
        
        $this->setId($ilDB->nextId("il_custom_block"));
        $query = "INSERT INTO il_custom_block (" .
            " id" .
            ", context_obj_id" .
            ", context_obj_type" .
            ", context_sub_obj_id" .
            ", context_sub_obj_type" .
            ", type" .
            ", title" .
            " ) VALUES (" .
            $ilDB->quote($this->getId(), "integer")
            . "," . $ilDB->quote($this->getContextObjId(), "integer")
            . "," . $ilDB->quote($this->getContextObjType(), "text")
            . "," . $ilDB->quote($this->getContextSubObjId(), "integer")
            . "," . $ilDB->quote($this->getContextSubObjType(), "text")
            . "," . $ilDB->quote($this->getType(), "text")
            . "," . $ilDB->quote($this->getTitle(), "text") . ")";
        $ilDB->manipulate($query);
    }

    /**
    * Read item from database.
    *
    */
    public function read()
    {
        $ilDB = $this->db;
        
        $query = "SELECT * FROM il_custom_block WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setContextObjId($rec["context_obj_id"]);
        $this->setContextObjType($rec["context_obj_type"]);
        $this->setContextSubObjId($rec["context_sub_obj_id"]);
        $this->setContextSubObjType($rec["context_sub_obj_type"]);
        $this->setType($rec["type"]);
        $this->setTitle($rec["title"]);
    }

    /**
    * Update item in database.
    *
    */
    public function update()
    {
        $ilDB = $this->db;
        
        $query = "UPDATE il_custom_block SET " .
            " context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
            ", context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
            ", context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
            ", context_sub_obj_type = " . $ilDB->quote($this->getContextSubObjType(), "text") .
            ", type = " . $ilDB->quote($this->getType(), "text") .
            ", title = " . $ilDB->quote($this->getTitle(), "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        
        $ilDB->manipulate($query);
    }

    /**
    * Delete item from database.
    *
    */
    public function delete()
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM il_custom_block" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        
        $ilDB->manipulate($query);
    }

    /**
    * Query getBlocksForContext
    *
    */
    public function querygetBlocksForContext()
    {
        $ilDB = $this->db;
        
        $query = "SELECT id, context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, type, title " .
            "FROM il_custom_block " .
            "WHERE " .
                "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                " AND context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
                " AND " . $ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true);
        //" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType(), "text")."";

        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        
        return $result;
    }

    /**
    * Query BlocksForContext
    *
    */
    public function queryBlocksForContext($a_include_sub_obj = true)
    {
        $ilDB = $this->db;
        
        $query = "SELECT id, context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, type, title " .
            "FROM il_custom_block " .
            "WHERE " .
                "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text");
        if ($a_include_sub_obj_id) {
            $query .= " AND context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
                " AND " . $ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true);
            //" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType(), "text")."";
        }
        //echo "$query";
        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        
        return $result;
    }

    /**
    * Query TitleForId
    *
    */
    public function queryTitleForId()
    {
        $ilDB = $this->db;
        die("ilCustomBlock::queryTitleForId is deprecated");
        /*
                $query = "SELECT id ".
                    "FROM il_custom_block ".
                    "WHERE "."";

                $set = $ilDB->query($query);
                $result = array();
                while($rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC))
                {
                    $result[] = $rec;
                }

                return $result;
        */
    }

    /**
    * Query CntBlockForContext
    *
    */
    public function queryCntBlockForContext()
    {
        $ilDB = $this->db;
        
        $query = "SELECT count(*) as cnt " .
            "FROM il_custom_block " .
            "WHERE " .
                "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                " AND context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
                " AND " . $ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true) .
                " AND type = " . $ilDB->quote($this->getType(), "text") . "";
                
        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        
        return $result;
    }
        
    public static function multiBlockQuery($a_context_obj_type, array $a_context_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT id, context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, type, title " .
            "FROM il_custom_block " .
            "WHERE " .
                $ilDB->in("context_obj_id", $a_context_obj_ids, "", "integer") .
                " AND context_obj_type = " . $ilDB->quote($a_context_obj_type, "text") .
            " ORDER BY title";
        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        
        return $result;
    }
}
