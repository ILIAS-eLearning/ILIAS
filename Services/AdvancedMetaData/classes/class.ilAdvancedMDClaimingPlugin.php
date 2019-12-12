<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
 * Abstract parent class for all advanced md claiming plugin classes.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAdvancedMetaData
 */
abstract class ilAdvancedMDClaimingPlugin extends ilPlugin
{
    //
    // plugin slot
    //
    
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }

    final public function getComponentName()
    {
        return "AdvancedMetaData";
    }

    final public function getSlot()
    {
        return "AdvancedMDClaiming";
    }

    final public function getSlotId()
    {
        return "amdc";
    }
    
    final protected function slotInit()
    {
        require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDPermissionHelper.php";
    }
    
    
    //
    // permission
    //
    
    /**
     * Check permission
     *
     * @param int $a_user_id
     * @param int $a_context_type
     * @param int $a_context_id
     * @param int $a_action_id
     * @param int $a_action_sub_id
     * @return bool
     */
    abstract public function checkPermission($a_user_id, $a_context_type, $a_context_id, $a_action_id, $a_action_sub_id);
    
    
    //
    // db update helper
    //
    
    /**
     * Check if record has db entry
     *
     * @param int $a_record_id
     * @return bool
     */
    public static function hasDBRecord($a_record_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT record_id FROM adv_md_record" .
            " WHERE record_id = " . $ilDB->quote($a_record_id, "integer"));
        return (bool) $ilDB->numRows($set);
    }
    
    /**
     * Create record db entry
     *
     * @param string $a_title
     * @param string $a_description
     * @param bool $a_active
     * @param array $a_obj_types
     * @return int record id
     */
    public static function createDBRecord($a_title, $a_description, $a_active, array $a_obj_types)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $record_id = $ilDB->nextId("adv_md_record");
        
        $fields = array(
            "record_id" => array("integer", $record_id),
            "import_id" => array("text", 'il_' . IL_INST_ID . '_adv_md_record_' . $record_id),
            "title" => array("text", trim($a_title)),
            "description" => array("text", trim($a_description)),
            "active" => array("integer", (int) $a_active)
        );
        $ilDB->insert("adv_md_record", $fields);
        
        self::saveRecordObjTypes($record_id, $a_obj_types);
        
        return $record_id;
    }
    
    /**
     * Validate object type
     *
     * @param string $a_obj_type
     * @param bool $a_is_substitution
     * @return bool
     */
    protected static function isValidObjType($a_obj_type, $a_is_substitution = false)
    {
        // ecs not supported yet
        $valid = array("crs", "cat", "book", "wiki", "glo", "orgu", "prg", 'grp', 'iass');

        if (!$a_is_substitution) {
            $valid[] = "orgu";
            $valid[] = "prg";
        }
        
        return in_array($a_obj_type, $valid);
    }
    
    /**
     * Save object type assignments for record
     *
     * @param int $a_record_id
     * @param array $a_obj_types
     */
    protected static function saveRecordObjTypes($a_record_id, array $a_obj_types)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
                
        foreach ($a_obj_types as $type) {
            if (!is_array($type)) {
                $type = strtolower(trim($type));
                $subtype  = "-";
            } else {
                $subtype = strtolower(trim($type[1]));
                $type = strtolower(trim($type[0]));
            }
                    
            if (self::isValidObjType($type)) {
                $fields = array(
                    "record_id" => array("integer", $a_record_id),
                    "obj_type" => array("text", $type),
                    "sub_type" => array("text", $subtype)
                );
                $ilDB->insert("adv_md_record_objs", $fields);
            }
        }
    }
    
    /**
     * Update record db entry
     *
     * @param int $a_record_id
     * @param string $a_title
     * @param string $a_description
     * @param bool $a_active
     * @param array $a_obj_types
     * @return bool
     */
    public static function updateDBRecord($a_record_id, $a_title, $a_description, $a_active, array $a_obj_types)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (self::hasDBRecord($a_record_id)) {
            $fields = array(
                "title" => array("text", trim($a_title)),
                "description" => array("text", trim($a_description)),
                "active" => array("integer", (int) $a_active)
            );
            $ilDB->update(
                "adv_md_record",
                $fields,
                array("record_id" => array("integer", $a_record_id))
            );
            
            $ilDB->manipulate("DELETE FROM adv_md_record_objs" .
                " WHERE record_id = " . $ilDB->quote($a_record_id, "integer"));

            self::saveRecordObjTypes($a_record_id, $a_obj_types);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete record db entry
     *
     * @param int $a_record_id
     * @return bool
     */
    public static function deleteDBRecord($a_record_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (self::hasDBRecord($a_record_id)) {
            $ilDB->manipulate("DELETE FROM adv_md_record" .
                " WHERE record_id = " . $ilDB->quote($a_record_id, "integer"));
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if field has db entry
     *
     * @param int $a_field_id
     * @return bool
     */
    public static function hasDBField($a_field_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT field_id FROM adv_mdf_definition" .
            " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
        return (bool) $ilDB->numRows($set);
    }
    
    
    /**
     * Get last position of record
     *
     * @see ilAdvancedMDFieldDefinition::getLastPosition()
     * @param int $a_record_id
     * @return int
     */
    protected static function getDBFieldLastPosition($a_record_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $sql = "SELECT max(position) pos" .
            " FROM adv_mdf_definition" .
            " WHERE record_id = " . $ilDB->quote($a_record_id, "integer");
        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $pos = $ilDB->fetchAssoc($set);
            return (int) $pos["pos"];
        }
        
        return 0;
    }
    
    /**
     * Create field db entry
     *
     * @param int $a_record_id
     * @param int $a_type
     * @param string $a_title
     * @param string $a_description
     * @param bool $a_searchable
     * @param array $a_definition
     * @return int field id
     */
    public static function createDBField($a_record_id, $a_type, $a_title, $a_description = null, $a_searchable = false, array $a_definition = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!self::hasDBRecord($a_record_id)) {
            return;
        }
        
        $field_id = $ilDB->nextId("adv_mdf_definition");
        
        // validating type
        $a_type = (int) $a_type;
        if ($a_type < 1 || $a_type > 8) {
            return;
        }
        
        $pos = self::getDBFieldLastPosition($a_record_id)+1;
        
        $fields = array(
            "record_id" => array("integer", $a_record_id),
            "field_id" => array("integer", $field_id),
            "import_id" => array("text", "il_" . IL_INST_ID . "_adv_md_field_" . $field_id),
            "field_type" => array("integer", $a_type),
            "position" => array("integer", $pos),
            "title" => array("text", trim($a_title)),
            "description" => array("text", trim($a_description)),
            "searchable" => array("integer", (int) $a_searchable)
        );
        if ($a_definition) {
            $fields["field_values"] = array("text", serialize($a_definition));
        }
        $ilDB->insert("adv_mdf_definition", $fields);
        
        return $field_id;
    }
    
    /**
     * Update field db entry
     *
     * @param int $a_field_id
     * @param string $a_title
     * @param string $a_description
     * @param bool $a_searchable
     * @param array $a_definition
     * @return bool
     */
    public static function updateDBField($a_field_id, $a_title, $a_description = null, $a_searchable = false, array $a_definition = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
                
        if (self::hasDBField($a_field_id)) {
            $fields = array(
                "field_id" => array("integer", $a_field_id),
                "title" => array("text", trim($a_title)),
                "description" => array("text", trim($a_description)),
                "searchable" => array("integer", (int) $a_searchable)
            );
            if ($a_definition) {
                $fields["field_values"] = array("text", serialize($a_definition));
            }
            $ilDB->update(
                "adv_mdf_definition",
                $fields,
                array("field_id" => array("integer", $a_field_id))
            );
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete field db entry
     *
     * @param int $a_field_id
     * @return bool
     */
    public static function deleteDBField($a_field_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (self::hasDBField($a_field_id)) {
            $ilDB->manipulate("DELETE FROM adv_mdf_definition" .
                " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
            return true;
        }
        
        return false;
    }
    
    /**
     * Get substitution DB data for object type
     *
     * @param string $a_obj_type
     * @param bool $a_include_field_data
     * @return array
     */
    protected static function getDBSubstitution($a_obj_type, $a_include_field_data = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT * FROM adv_md_substitutions" .
            " WHERE obj_type = " . $ilDB->quote($a_obj_type, "text"));
        if ($ilDB->numRows($set)) {
            $res = $ilDB->fetchAssoc($set);
            $res["hide_description"] = array("integer", (bool) $res["hide_description"]);
            $res["hide_field_names"] = array("integer", (bool) $res["hide_field_names"]);
                        
            if ($a_include_field_data) {
                $res["substitution"] = array("text", (array) unserialize($res["substitution"]));
            } else {
                unset($res["substitution"]);
            }
            unset($res["obj_type"]);
            
            return $res;
        }
    }
    
    /**
     * Set substitution DB entry (for object type)
     *
     * @param string $a_obj_type
     * @param bool $a_show_description
     * @param bool $a_show_field_names
     * @return bool
     */
    public static function setDBSubstitution($a_obj_type, $a_show_description, $a_show_field_names)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type);
            
            $create = false;
            if (!$fields) {
                $create = true;
                $fields = array("obj_type" => array("text", $a_obj_type));
            }
            
            $fields["hide_description"] = array("integer", !(bool) $a_show_description);
            $fields["hide_field_names"] = array("integer", !(bool) $a_show_field_names);
            
            if ($create) {
                $ilDB->insert("adv_md_substitutions", $fields);
            } else {
                $ilDB->update(
                    "adv_md_substitutions",
                    $fields,
                    array("obj_type" => array("text", $a_obj_type))
                );
            }
            
            return true;
        }
        return false;
    }
    
    /**
     * Is substitution active for field in object type
     *
     * @param string $a_obj_type
     * @param int $a_field_id
     * @return bool
     */
    public static function hasDBFieldSubstitution($a_obj_type, $a_field_id)
    {
        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type, true);
            $fields = $fields["substitution"][1];
            foreach ($fields as $field) {
                if ($field["field_id"] == $a_field_id) {
                    return true;
                }
            }
            return false;
        }
    }
    
    /**
     * Update field substitution entry in DB
     *
     * @param string $a_obj_type
     * @param int $a_field_id
     * @param bool $a_bold
     * @param bool $a_newline
     * @return bool
     */
    public static function setDBFieldSubstitution($a_obj_type, $a_field_id, $a_bold = false, $a_newline = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type, true);
            if (!$fields) {
                self::setDBSubstitution($a_obj_type, true, true);
                $fields = array();
            } else {
                $fields = $fields["substitution"][1];
            }
            
            $found = false;
            foreach ($fields as $idx => $field) {
                if ($field["field_id"] == $a_field_id) {
                    $fields[$idx]["bold"] = (bool) $a_bold;
                    $fields[$idx]["newline"] = (bool) $a_newline;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $fields[] = array(
                    "field_id" => $a_field_id
                    ,"bold" => (bool) $a_bold
                    ,"newline" => (bool) $a_newline
                );
            }
            
            $fields = array("substitution"=>array("text", serialize($fields)));
            $ilDB->update(
                "adv_md_substitutions",
                $fields,
                array("obj_type" => array("text", $a_obj_type))
            );
        }
        return false;
    }
    
    /**
     * Remove field substitution entry in DB
     *
     * @param string $a_obj_type
     * @param int $a_field_id
     * @return bool
     */
    public static function removeDBFieldSubstitution($a_obj_type, $a_field_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (self::isValidObjType($a_obj_type, true)) {
            $fields = self::getDBSubstitution($a_obj_type, true);
            if (!$fields) {
                return true;
            } else {
                $fields = $fields["substitution"][1];
            }
            
            $found = false;
            foreach ($fields as $idx => $field) {
                if ($field["field_id"] == $a_field_id) {
                    unset($fields[$idx]);
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $fields = array("substitution"=>array("text", serialize($fields)));
                $ilDB->update(
                    "adv_md_substitutions",
                    $fields,
                    array("obj_type" => array("text", $a_obj_type))
                );
            }
            return true;
        }
        return false;
    }
}
