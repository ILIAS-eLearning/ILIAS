<?php declare(strict_types = 1);

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
 *********************************************************************/

/**
 * This is the super class of all custom blocks.
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilCustomBlock
{
    protected ilDBInterface $db;
    protected int $id = 0;
    protected int $context_obj_id = 0;
    protected string $context_obj_type = "";
    protected int $context_sub_obj_id = 0;
    protected string $context_sub_obj_type = "";
    protected string $type = "";
    protected string $title = "";

    public function __construct(
        int $a_id = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    public function setId(int $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setContextObjId(int $a_context_obj_id) : void
    {
        $this->context_obj_id = $a_context_obj_id;
    }

    public function getContextObjId() : int
    {
        return $this->context_obj_id;
    }

    public function setContextObjType(string $a_context_obj_type) : void
    {
        $this->context_obj_type = $a_context_obj_type;
    }

    public function getContextObjType() : string
    {
        return $this->context_obj_type;
    }

    public function setContextSubObjId(int $a_context_sub_obj_id) : void
    {
        $this->context_sub_obj_id = $a_context_sub_obj_id;
    }

    public function getContextSubObjId() : int
    {
        return $this->context_sub_obj_id;
    }

    public function setContextSubObjType(string $a_context_sub_obj_type) : void
    {
        $this->context_sub_obj_type = $a_context_sub_obj_type;
    }

    public function getContextSubObjType() : string
    {
        return $this->context_sub_obj_type;
    }

    /**
     * @param	string	$a_type	Type of block.
     */
    public function setType(string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function create() : void
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

    public function read() : void
    {
        $ilDB = $this->db;
        
        $query = "SELECT * FROM il_custom_block WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setContextObjId((int) $rec["context_obj_id"]);
        $this->setContextObjType($rec["context_obj_type"]);
        $this->setContextSubObjId((int) $rec["context_sub_obj_id"]);
        $this->setContextSubObjType((string) $rec["context_sub_obj_type"]);
        $this->setType($rec["type"]);
        $this->setTitle((string) $rec["title"]);
    }

    public function update() : void
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

    public function delete() : void
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM il_custom_block" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        
        $ilDB->manipulate($query);
    }

    /**
     * Query BlocksForContext
     */
    public function queryBlocksForContext(
        bool $a_include_sub_obj = true
    ) : array {
        $ilDB = $this->db;
        
        $query = "SELECT id, context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, type, title " .
            "FROM il_custom_block " .
            "WHERE " .
                "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text");
        if ($a_include_sub_obj) {
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

    public function queryCntBlockForContext() : array
    {
        $ilDB = $this->db;
        
        $query = "SELECT count(*) as cnt " .
            "FROM il_custom_block " .
            "WHERE " .
                "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                " AND context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
                " AND " . $ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true) .
                " AND type = " . $ilDB->quote($this->getType(), "text");
                
        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        
        return $result;
    }
        
    public static function multiBlockQuery(
        string $a_context_obj_type,
        array $a_context_obj_ids
    ) : array {
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
