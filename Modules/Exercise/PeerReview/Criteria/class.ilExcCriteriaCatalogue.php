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
 *********************************************************************/
 
/**
 * Class ilExcCriteriaCatalogue
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCriteriaCatalogue
{
    protected ilDBInterface $db;
    protected ?int $id = null;
    protected ?int $parent = null;
    protected ?string $title = null;
    protected int $pos = 0;
    
    public function __construct(int $a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read($a_id);
    }

    /**
     * @return self[]
     */
    public static function getInstancesByParentId(int $a_parent_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $set = $ilDB->query("SELECT *" .
            " FROM exc_crit_cat" .
            " WHERE parent = " . $ilDB->quote($a_parent_id, "integer") .
            " ORDER BY pos");
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj = new self();
            $obj->importFromDB($row);
            $res[$obj->getId()] = $obj;
        }
        
        return $res;
    }
    
    
    //
    // properties
    //
    
    public function getId() : ?int
    {
        return $this->id;
    }
    
    protected function setId(int $a_id) : void
    {
        $this->id = $a_id;
    }
    
    public function setParent(?int $a_value) : void
    {
        $this->parent = $a_value;
    }
    
    public function getParent() : ?int
    {
        return $this->parent;
    }
    
    public function setTitle(?string $a_value) : void
    {
        $this->title = $a_value;
    }
    
    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setPosition(int $a_value) : void
    {
        $this->pos = $a_value;
    }
    
    public function getPosition() : int
    {
        return $this->pos;
    }
    
    
    //
    // CRUD
    //
    
    protected function importFromDB(array $a_row) : void
    {
        $this->setId((int) $a_row["id"]);
        $this->setParent((int) $a_row["parent"]);
        $this->setTitle((string) $a_row["title"]);
        $this->setPosition((int) $a_row["pos"]);
    }
    
    protected function getDBProperties() : array
    {
        return array(
            "title" => array("text", $this->getTitle())
            ,"pos" => array("integer", $this->getPosition())
        );
    }

    protected function getLastPosition() : int
    {
        $ilDB = $this->db;
        
        if (!$this->getParent()) {
            return 0;
        }
        
        $set = $ilDB->query("SELECT MAX(pos) pos" .
            " FROM exc_crit_cat" .
            " WHERE parent = " . $ilDB->quote($this->getParent(), "integer"));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["pos"];
    }
    
    protected function read(?int $a_id) : void
    {
        $ilDB = $this->db;
        
        if ($a_id > 0) {
            $set = $ilDB->query("SELECT *" .
                " FROM exc_crit_cat" .
                " WHERE id = " . $ilDB->quote($a_id, "integer"));
            if ($ilDB->numRows($set) !== 0) {
                $row = $ilDB->fetchAssoc($set);
                $this->importFromDB($row);
            }
        }
    }
    
    public function save() : void
    {
        $ilDB = $this->db;
        
        if ($this->id) {
            $this->update();
            return;
        }
        
        $this->id = $ilDB->nextId("exc_crit_cat");
        
        $fields = $this->getDBProperties();
        $fields["parent"] = array("integer", $this->getParent());
        $fields["pos"] = array("integer", $this->getLastPosition() + 10);
        $fields["id"] = array("integer", $this->id);
        
        $ilDB->insert("exc_crit_cat", $fields);
    }
    
    public function update() : void
    {
        $ilDB = $this->db;
        
        if (!$this->id) {
            $this->save();
            return;
        }
        
        $primary = array("id" => array("integer", $this->id));
        $ilDB->update("exc_crit_cat", $this->getDBProperties(), $primary);
    }
    
    public function delete() : void
    {
        $ilDB = $this->db;
        
        if (!$this->id) {
            return;
        }
        
        ilExcCriteria::deleteByParent($this->id);
                
        $ilDB->manipulate("DELETE FROM exc_crit_cat" .
            " WHERE id = " . $ilDB->quote($this->id, "integer"));
    }
    
    public static function deleteByParent(int $a_parent_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($a_parent_id <= 0) {
            return;
        }
        
        $ilDB->manipulate("DELETE FROM exc_crit" .
            " WHERE parent = " . $ilDB->quote($a_parent_id, "integer"));
    }
    
    public function cloneObject(int $a_target_parent_id) : int
    {
        $new_obj = new self();
        $new_obj->setParent($a_target_parent_id);
        $new_obj->setTitle($this->getTitle());
        $new_obj->setPosition($this->getPosition());
        $new_obj->save();
        
        foreach (ilExcCriteria::getInstancesByParentId($this->getId()) as $crit) {
            $crit->cloneObject($new_obj->getId());
        }
        
        return $new_obj->getId();
    }
}
