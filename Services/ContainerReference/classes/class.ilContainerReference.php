<?php declare(strict_types=1);

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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerReference extends ilObject
{
    public const TITLE_TYPE_REUSE = 1;
    public const TITLE_TYPE_CUSTOM = 2;

    protected ilObjUser $user;
    protected ?int $target_id = null;
    protected ?int $target_ref_id = null;
    protected int $title_type = self::TITLE_TYPE_REUSE;
    
    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->user = $DIC->user();
        parent::__construct($a_id, $a_call_by_reference);
    }
    
    public static function _lookupTargetId(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM container_reference " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->target_obj_id;
        }
        return $a_obj_id;
    }
    
    public static function _lookupTargetRefId(int $a_obj_id) : ?int
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT ref_id FROM object_reference obr " .
            "JOIN container_reference cr ON obr.obj_id = cr.target_obj_id " .
            "WHERE cr.obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->ref_id;
        }
        return null;
    }
     
    public static function _lookupTitle(int $obj_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();
         
        $query = 'SELECT title,title_type FROM container_reference cr ' .
                 'JOIN object_data od ON cr.obj_id = od.obj_id ' .
                 'WHERE cr.obj_id = ' . $ilDB->quote($obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ((int) $row->title_type === self::TITLE_TYPE_CUSTOM) {
                return (string) $row->title;
            }
        }
        return self::_lookupTargetTitle($obj_id);
    }
    
    public static function _lookupTargetTitle(int $a_obj_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT title FROM object_data od " .
            "JOIN container_reference cr ON target_obj_id = od.obj_id " .
            "WHERE cr.obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (string) $row->title;
        }
        return '';
    }
    
    public static function _lookupSourceId(int $a_target_id) : ?int
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM container_reference " .
            "WHERE target_obj_id = " . $ilDB->quote($a_target_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->obj_id;
        }
        return null;
    }
    
    /**
     * Get ids of all container references that target the object with the
     * given id.
     * @return int[] obj_ids of references
     */
    public static function _lookupSourceIds(int $a_target_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM container_reference " .
            "WHERE target_obj_id = " . $ilDB->quote($a_target_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $ret = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ret[] = (int) $row->obj_id;
        }
        return $ret;
    }
    
    public function getTargetId() : ?int
    {
        return $this->target_id;
    }
    
    public function setTargetId(int $a_target_id) : void
    {
        $this->target_id = $a_target_id;
    }
    
    public function setTargetRefId(int $a_id) : void
    {
        $this->target_ref_id = $a_id;
    }
    
    public function getTargetRefId() : ?int
    {
        return $this->target_ref_id;
    }
    
    public function getTitleType() : int
    {
        return $this->title_type;
    }
    
    public function setTitleType(int $type) : void
    {
        $this->title_type = $type;
    }
    
    public function read() : void
    {
        $ilDB = $this->db;
        
        parent::read();
        
        $query = "SELECT * FROM container_reference " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTargetId((int) $row->target_obj_id);
            $this->setTitleType((int) $row->title_type);
        }
        if ($this->getTargetId()) {// might be null...
            $ref_ids = ilObject::_getAllReferences($this->getTargetId());
            $this->setTargetRefId(current($ref_ids));
        
            if ($this->getTitleType() === self::TITLE_TYPE_REUSE) {
                $this->title = ilObject::_lookupTitle($this->getTargetId());
            }
        }
    }

    public function getPresentationTitle() : string
    {
        if ($this->getTitleType() === self::TITLE_TYPE_CUSTOM) {
            return $this->getTitle();
        }

        return $this->lng->txt('reference_of') . ' ' . $this->getTitle();
    }
    
    public function update() : bool
    {
        $ilDB = $this->db;
        
        parent::update();
        
        $query = "DELETE FROM container_reference " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $ilDB->manipulate($query);
        
        $query = "INSERT INTO container_reference (obj_id, target_obj_id, title_type) " .
            "VALUES( " .
            $ilDB->quote($this->getId(), 'integer') . ", " .
            $ilDB->quote($this->getTargetId(), 'integer') . ", " .
            $ilDB->quote($this->getTitleType(), 'integer') . ' ' .
            ")";
        $ilDB->manipulate($query);
        return true;
    }
    
    public function delete() : bool
    {
        $ilDB = $this->db;

        if (!parent::delete()) {
            return false;
        }

        $query = "DELETE FROM container_reference " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $ilDB->manipulate($query);
        
        return true;
    }
    
    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false) : ?ilObject
    {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $new_obj->setTargetId($this->getTargetId());
        $new_obj->setTitleType($this->getTitleType());
        $new_obj->update();
        return $new_obj;
    }
}
