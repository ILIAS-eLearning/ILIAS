<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
 * Abstract parent class for all udf claiming plugin classes.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
abstract class ilUDFClaimingPlugin extends ilPlugin
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
        return "User";
    }

    final public function getSlot()
    {
        return "UDFClaiming";
    }

    final public function getSlotId()
    {
        return "udfc";
    }
    
    final protected function slotInit()
    {
        require_once "Services/User/classes/class.ilUDFPermissionHelper.php";
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
     * Check if field has db entry
     *
     * @param int $a_field_id
     * @return bool
     */
    public static function hasDBField($a_field_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT field_id FROM udf_definition" .
            " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
        return (bool) $ilDB->numRows($set);
    }
    
    /**
     * Get existing field values
     *
     * @param int $a_field_id
     * @return array
     */
    protected static function getDBField($a_field_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT * FROM udf_definition" .
            " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
        return $ilDB->fetchAssoc($set);
    }
    
    /**
     * Validate field type
     *
     * @param string $a_field_type
     * @return bool
     */
    protected static function isValidFieldType($a_field_type)
    {
        // needed for the type constants
        require_once "Services/User/classes/class.ilUserDefinedFields.php";
        
        $valid = array(UDF_TYPE_TEXT, UDF_TYPE_SELECT, UDF_TYPE_WYSIWYG);

        return in_array($a_field_type, $valid);
    }
    
    /**
     * Convert access array to DB columns
     *
     * @param array $fields
     * @param array $a_access
     */
    protected static function handleAccesss(array &$fields, array $a_access = null, array $a_existing = null)
    {
        $map = array("visible", "changeable", "searchable", "required", "export",
            "course_export", "group_export", "registration_visible", "visible_lua",
            "changeable_lua", "certificate");
        foreach ($map as $prop) {
            if (isset($a_access[$prop])) {
                $fields[$prop] = array("integer", (int) $a_access[$prop]);
            } elseif (isset($a_existing[$prop])) {
                $fields[$prop] = array("integer", (int) $a_existing[$prop]);
            } else {
                $fields[$prop] = array("integer", 0);
            }
        }
    }
    
    /**
     * Create field db entry
     *
     * @param int $a_type
     * @param string $a_title
     * @param array $a_access
     * @param array $a_options
     * @return int field id
     */
    public static function createDBField($a_type, $a_title, array $a_access = null, array $a_options = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $field_id = $ilDB->nextId("udf_definition");
        
        // validating type
        $a_type = (int) $a_type;
        if (!self::isValidFieldType($a_type)) {
            return;
        }
        
        if ($a_type != UDF_TYPE_SELECT) {
            $a_options = null;
        }
        
        // :TODO: check unique title?
        
        $fields = array(
            "field_id" => array("integer", $field_id),
            "field_type" => array("integer", $a_type),
            "field_name" => array("text", trim($a_title)),
            "field_values" => array("text", serialize((array) $a_options))
        );
        
        self::handleAccesss($fields, $a_access);
        
        $ilDB->insert("udf_definition", $fields);
        
        return $field_id;
    }
    
    /**
     * Update field db entry
     *
     * @param int $a_field_id
     * @param string $a_title
     * @param array $a_access
     * @param array $a_options
     * @return bool
     */
    public static function updateDBField($a_field_id, $a_title, array $a_access = null, array $a_options = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
                
        if (self::hasDBField($a_field_id)) {
            $old = self::getDBField($a_field_id);
            
            if ($old["field_type"] != UDF_TYPE_SELECT) {
                $a_options = null;
            }
            
            $fields = array(
                "field_name" => array("text", trim($a_title)),
                "field_values" => array("text", serialize((array) $a_options))
            );
            
            self::handleAccesss($fields, $a_access, $old);
            
            $ilDB->update(
                "udf_definition",
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
            // :TODO: we are not deleting any values here
            
            $ilDB->manipulate("DELETE FROM udf_definition" .
                " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
            return true;
        }
        
        return false;
    }
}
