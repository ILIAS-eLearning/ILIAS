<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExcCriteria
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
abstract class ilExcCriteria
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $id; // [int]
    protected $parent; // [int]
    protected $title; // [string]
    protected $desc; // [string]
    protected $required; // [bool]
    protected $pos; // [int]
    protected $def; // [string]
    
    protected $form; // [ilPropertyFormGUI]
    protected $ass; // [ilExAssignment]
    protected $giver_id; // [int]
    protected $peer_id; // [int]
    
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }
    
    public static function getInstanceById($a_id)
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
    }
    
    public static function getInstancesByParentId($a_parent_id)
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
    
    public static function getTypesMap()
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
    
    public function getTranslatedType()
    {
        $map = $this->getTypesMap();
        return $map[$this->getType()];
    }
    
    public static function getInstanceByType($a_type)
    {
        $class = "ilExcCriteria" . ucfirst($a_type);
        include_once "Modules/Exercise/classes/class." . $class . ".php";
        return new $class;
    }
    
    
    //
    // properties
    //
    
    public function getId()
    {
        return $this->id;
    }
    
    protected function setId($a_id)
    {
        $this->id = (int) $a_id;
    }
    
    abstract public function getType();
    
    public function setParent($a_value)
    {
        $this->parent = ($a_value !== null)
            ? (int) $a_value
            : null;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
    public function setTitle($a_value)
    {
        $this->title = ($a_value !== null)
            ? trim($a_value)
            : null;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setDescription($a_value)
    {
        $this->desc = ($a_value !== null)
            ? trim($a_value)
            : null;
    }
    
    public function getDescription()
    {
        return $this->desc;
    }
    
    public function setRequired($a_value)
    {
        $this->required = (bool) $a_value;
    }
    
    public function isRequired()
    {
        return $this->required;
    }

    public function setPosition($a_value)
    {
        $this->pos = (int) $a_value;
    }
    
    public function getPosition()
    {
        return $this->pos;
    }
    
    protected function setDefinition(array $a_value = null)
    {
        $this->def = $a_value;
    }
    
    protected function getDefinition()
    {
        return $this->def;
    }
    
    public function importDefinition($a_def, $a_def_json)
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
            $a_def = @unserialize($a_def, false);
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
        $this->setId($a_row["id"]);
        $this->setParent($a_row["parent"]);
        $this->setTitle($a_row["title"]);
        $this->setDescription($a_row["descr"]);
        $this->setRequired($a_row["required"]);
        $this->setPosition($a_row["pos"]);
        $this->setDefinition($a_row["def"]
                ? unserialize($a_row["def"])
                : null);
    }
    
    protected function getDBProperties()
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
    protected function getLastPosition()
    {
        $ilDB = $this->db;
        
        if (!$this->getParent()) {
            return;
        }
        
        $set = $ilDB->query("SELECT MAX(pos) pos" .
            " FROM exc_crit" .
            " WHERE parent = " . $ilDB->quote($this->getParent(), "integer"));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["pos"];
    }
    
    public function save()
    {
        $ilDB = $this->db;
        
        if ($this->id) {
            return $this->update();
        }
        
        $this->id = $ilDB->nextId("exc_crit");
        
        $fields = $this->getDBProperties();
        
        $fields["id"] = array("integer", $this->id);
        $fields["type"] = array("text", $this->getType());
        $fields["parent"] = array("integer", $this->getParent());
        $fields["pos"] = array("integer", $this->getLastPosition() + 10);
        
        $ilDB->insert("exc_crit", $fields);
    }
    
    public function update()
    {
        $ilDB = $this->db;
        
        if (!$this->id) {
            return $this->save();
        }
        
        $primary = array("id" => array("integer", $this->id));
        $ilDB->update("exc_crit", $this->getDBProperties(), $primary);
    }
    
    public function delete()
    {
        $ilDB = $this->db;
        
        if (!$this->id) {
            return;
        }
                
        $ilDB->manipulate("DELETE FROM exc_crit" .
            " WHERE id = " . $ilDB->quote($this->id, "integer"));
    }
    
    public static function deleteByParent($a_parent_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if (!(int) $a_parent_id) {
            return;
        }
        
        $ilDB->manipulate("DELETE FROM exc_crit" .
            " WHERE parent = " . $ilDB->quote($a_parent_id, "integer"));
    }
    
    public function cloneObject($a_target_parent_id)
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
    
    public function initCustomForm(ilPropertyFormGUI $a_form)
    {
        // type-specific
    }
    
    public function exportCustomForm(ilPropertyFormGUI $a_form)
    {
        // type-specific
    }
    
    public function importCustomForm(ilPropertyFormGUI $a_form)
    {
        // type-specific
    }
    
    
    // PEER REVIEW
    
    public function setPeerReviewContext(ilExAssignment $a_ass, $a_giver_id, $a_peer_id, ilPropertyFormGUI $a_form = null)
    {
        $this->form = $a_form;
        $this->ass = $a_ass;
        $this->giver_id = $a_giver_id;
        $this->peer_id = $a_peer_id;
    }
    
    abstract public function addToPeerReviewForm($a_value = null);
    
    abstract public function importFromPeerReviewForm();
    
    public function updateFromAjax()
    {
        // type-specific
    }
    
    public function validate($a_value)
    {
        return true;
    }
    
    abstract public function hasValue($a_value);
    
    abstract public function getHTML($a_value);
        
    public function resetReview()
    {
        // type-specific (only needed for data not kept in exc_assignment_peer)
    }
}
