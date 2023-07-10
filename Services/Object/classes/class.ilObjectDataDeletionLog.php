<?php

declare(strict_types=1);

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
 * Class ilBadgeManagementGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilObjectDataDeletionLog
{
    public static function add(ilObject $object): void
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

    public static function get(int $a_object_id): ?array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql =
            "SELECT obj_id, title, tstamp, type, description" . PHP_EOL
            . "FROM object_data_del" . PHP_EOL
            . "WHERE obj_id = " . $ilDB->quote($a_object_id, "integer") . PHP_EOL
        ;

        $set = $ilDB->query($sql);
        return $ilDB->fetchAssoc($set);
    }
}
