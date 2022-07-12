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
 * Abstract parent class for all udf claiming plugin classes.
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilUDFClaimingPlugin extends ilPlugin
{
    //
    // permission
    //
    
    /**
     * Check permission
     */
    abstract public function checkPermission(
        int $a_user_id,
        int $a_context_type,
        int $a_context_id,
        int $a_action_id,
        int $a_action_sub_id
    ) : bool;
    
    
    //
    // db update helper
    //
    
    /**
     * Check if field has db entry
     */
    public static function hasDBField(string $a_field_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT field_id FROM udf_definition" .
            " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
        return (bool) $ilDB->numRows($set);
    }
    
    /**
     * Get existing field values
     */
    protected static function getDBField(string $a_field_id) : array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT * FROM udf_definition" .
            " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
        return $ilDB->fetchAssoc($set);
    }
    
    /**
     * Validate field type
     */
    protected static function isValidFieldType(int $a_field_type) : bool
    {
        $valid = array(UDF_TYPE_TEXT, UDF_TYPE_SELECT, UDF_TYPE_WYSIWYG);
        return in_array($a_field_type, $valid);
    }
    
    /**
     * Convert access array to DB columns
     */
    protected static function handleAccesss(
        array &$fields,
        ?array $a_access = null,
        ? array $a_existing = null
    ) : void {
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
     */
    public static function createDBField(
        int $a_type,
        string $a_title,
        array $a_access = null,
        array $a_options = null
    ) : ?int {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $field_id = $ilDB->nextId("udf_definition");
        
        // validating type
        if (!self::isValidFieldType($a_type)) {
            return null;
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
     */
    public static function updateDBField(
        int $a_field_id,
        string $a_title,
        array $a_access = null,
        array $a_options = null
    ) : bool {
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
     */
    public static function deleteDBField(int $a_field_id) : bool
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
