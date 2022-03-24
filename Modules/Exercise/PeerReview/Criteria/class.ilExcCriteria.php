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
 * Class ilExcCriteria.
 *
 * Note: This class does stuff on application and gui layer and
 * should be divided in multiple interfaces.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilExcCriteria
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilDBInterface $db;
    protected ?int $id = null;
    protected ?int $parent = null;
    protected string $title = "";
    protected string $desc = "";
    protected bool $required = false;
    protected int $pos = 0;
    protected ?array $def = null;
    protected ?ilPropertyFormGUI $form = null;
    protected ilExAssignment $ass;
    protected int $giver_id = 0;
    protected int $peer_id = 0;
    
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    public static function getInstanceById(int $a_id) : ?ilExcCriteria
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT *" .
            " FROM exc_crit" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            $obj = self::getInstanceByType($row["type"]);
            $obj->importFromDB($row);
            return $obj;
        }

        return null;
    }

    /**
     * @param int $a_parent_id
     * @return ilExcCriteria[]
     */
    public static function getInstancesByParentId(int $a_parent_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $set = $ilDB->query("SELECT *" .
            " FROM exc_crit" .
            " WHERE parent = " . $ilDB->quote($a_parent_id, "integer") .
            " ORDER BY pos");
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj = self::getInstanceByType($row["type"]);
            $obj->importFromDB($row);
            $res[$obj->getId()] = $obj;
        }
        
        return $res;
    }
    
    
    //
    // type(s)
    //
    
    public static function getTypesMap() : array
    {
        global $DIC;

        $lng = $DIC->language();
        
        return array(
            "bool" => $lng->txt("exc_criteria_type_bool")
            ,"rating" => $lng->txt("exc_criteria_type_rating")
            ,"text" => $lng->txt("exc_criteria_type_text")
            ,"file" => $lng->txt("exc_criteria_type_file")
        );
    }
    
    public function getTranslatedType() : string
    {
        $map = $this->getTypesMap();
        return $map[$this->getType()];
    }
    
    public static function getInstanceByType(string $a_type) : ilExcCriteria
    {
        $class = "ilExcCriteria" . ucfirst($a_type);
        return new $class();
    }
    
    
    //
    // properties
    //
    
    public function getId() : ?int
    {
        return $this->id;
    }
    
    protected function setId(?int $a_id) : void
    {
        $this->id = $a_id;
    }
    
    abstract public function getType() : string;
    
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
    
    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function setDescription(?string $a_value) : void
    {
        $this->desc = $a_value;
    }
    
    public function getDescription() : string
    {
        return $this->desc;
    }
    
    public function setRequired(bool $a_value) : void
    {
        $this->required = $a_value;
    }
    
    public function isRequired() : bool
    {
        return $this->required;
    }

    public function setPosition(int $a_value) : void
    {
        $this->pos = $a_value;
    }
    
    public function getPosition() : int
    {
        return $this->pos;
    }
    
    protected function setDefinition(?array $a_value = null)
    {
        $this->def = $a_value;
    }
    
    protected function getDefinition() : ?array
    {
        return $this->def;
    }
    
    public function importDefinition(string $a_def, string $a_def_json) : void
    {
        // see #23711
        // use json, if given
        if ($a_def_json != "") {
            $def = json_decode($a_def_json, true);
            if (is_array($def)) {
                $this->setDefinition($def);
            }
            return;
        }

        // use unserialize only if php > 7
        if ($a_def != "" && version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $a_def = unserialize($a_def, false);
            if (is_array($a_def)) {
                $this->setDefinition($a_def);
            }
        }
    }
    
    
    //
    // CRUD
    //
    
    protected function importFromDB(array $a_row)
    {
        $this->setId((int) $a_row["id"]);
        $this->setParent((int) $a_row["parent"]);
        $this->setTitle((string) $a_row["title"]);
        $this->setDescription((string) $a_row["descr"]);
        $this->setRequired((bool) $a_row["required"]);
        $this->setPosition((int) $a_row["pos"]);
        $this->setDefinition((string) $a_row["def"]
                ? unserialize($a_row["def"])
                : null);
    }
    
    protected function getDBProperties() : array
    {
        return array(
            "type" => array("text", $this->getType())
            ,"title" => array("text", $this->getTitle())
            ,"descr" => array("text", $this->getDescription())
            ,"required" => array("integer", $this->isRequired())
            ,"pos" => array("integer", $this->getPosition())
            ,"def" => array("text", is_array($this->getDefinition())
                ? serialize($this->getDefinition())
                : null)
        );
    }

    protected function getLastPosition() : int
    {
        $ilDB = $this->db;
        
        if (!$this->getParent()) {
            return 0;
        }
        
        $set = $ilDB->query("SELECT MAX(pos) pos" .
            " FROM exc_crit" .
            " WHERE parent = " . $ilDB->quote($this->getParent(), "integer"));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["pos"];
    }
    
    public function save() : void
    {
        $ilDB = $this->db;
        
        if ($this->id) {
            $this->update();
            return;
        }
        
        $this->id = $ilDB->nextId("exc_crit");
        
        $fields = $this->getDBProperties();
        
        $fields["id"] = array("integer", $this->id);
        $fields["type"] = array("text", $this->getType());
        $fields["parent"] = array("integer", $this->getParent());
        $fields["pos"] = array("integer", $this->getLastPosition() + 10);
        
        $ilDB->insert("exc_crit", $fields);
    }
    
    public function update() : void
    {
        $ilDB = $this->db;
        
        if (!$this->id) {
            $this->save();
            return;
        }
        
        $primary = array("id" => array("integer", $this->id));
        $ilDB->update("exc_crit", $this->getDBProperties(), $primary);
    }
    
    public function delete() : void
    {
        $ilDB = $this->db;
        
        if (!$this->id) {
            return;
        }
                
        $ilDB->manipulate("DELETE FROM exc_crit" .
            " WHERE id = " . $ilDB->quote($this->id, "integer"));
    }
    
    public static function deleteByParent(int $a_parent_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if (!$a_parent_id) {
            return;
        }
        
        $ilDB->manipulate("DELETE FROM exc_crit" .
            " WHERE parent = " . $ilDB->quote($a_parent_id, "integer"));
    }
    
    public function cloneObject(int $a_target_parent_id) : ?int
    {
        $new_obj = ilExcCriteria::getInstanceByType($this->getType());
        $new_obj->setParent($a_target_parent_id);
        $new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setRequired($this->isRequired());
        $new_obj->setPosition($this->getPosition());
        $new_obj->setDefinition($this->getDefinition());
        $new_obj->save();
        
        return $new_obj->getId();
    }
    
    
    //
    // ASSIGNMENT EDITOR
    //
    
    public function initCustomForm(ilPropertyFormGUI $a_form) : void
    {
        // type-specific
    }
    
    public function exportCustomForm(ilPropertyFormGUI $a_form) : void
    {
        // type-specific
    }
    
    public function importCustomForm(ilPropertyFormGUI $a_form) : void
    {
        // type-specific
    }
    
    
    // PEER REVIEW
    
    public function setPeerReviewContext(
        ilExAssignment $a_ass,
        int $a_giver_id,
        int $a_peer_id,
        ilPropertyFormGUI $a_form = null
    ) {
        $this->form = $a_form;
        $this->ass = $a_ass;
        $this->giver_id = $a_giver_id;
        $this->peer_id = $a_peer_id;
    }
    
    abstract public function addToPeerReviewForm($a_value = null) : void;
    
    abstract public function importFromPeerReviewForm();
    
    public function updateFromAjax() : string
    {
        return "";
    }
    
    public function validate($a_value) : bool
    {
        return true;
    }
    
    abstract public function hasValue($a_value);
    
    abstract public function getHTML($a_value) : string;
        
    public function resetReview()
    {
        // type-specific (only needed for data not kept in exc_assignment_peer)
    }
}
