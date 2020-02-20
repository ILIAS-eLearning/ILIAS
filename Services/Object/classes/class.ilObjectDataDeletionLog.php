<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeManagementGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilObjectDataDeletionLog
{
    public static function add(ilObject $a_object)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->insert("object_data_del", array(
            "obj_id" => array("integer", $a_object->getId()),
            "type" => array("text", $a_object->getType()),
            "title" => array("text", $a_object->getTitle()),
            "description" => array("clob", $a_object->getLongDescription()),
            "tstamp" => array("integer", time())
        ));
    }
    
    public static function get($a_object_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT * FROM object_data_del" .
            " WHERE obj_id = " . $ilDB->quote($a_object_id, "integer"));
        return $ilDB->fetchAssoc($set);
    }
}
