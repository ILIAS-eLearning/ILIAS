<?php declare(strict_types=1);

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
    public static function add(ilObject $object) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $values = [
            "obj_id" => ["integer", $object->getId()],
            "title" => ["text", $object->getTitle()],
            "tstamp" => ["integer", time()],
            "type" => ["text", $object->getType()],
            "description" => ["clob", $object->getLongDescription()]
        ];
        
        $ilDB->insert("object_data_del", $values);
    }
    
    public static function get(int $a_object_id) : ?array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql =
            "SELECT obj_id, title, tstamp, type, description" . PHP_EOL
            ."FROM object_data_del" . PHP_EOL
            ."WHERE obj_id = " . $ilDB->quote($a_object_id, "integer") . PHP_EOL
        ;

        $set = $ilDB->query($sql);
        return $ilDB->fetchAssoc($set);
    }
}
