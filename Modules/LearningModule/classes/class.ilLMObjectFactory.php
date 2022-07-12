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
 * Class ilLMObjectFactory
 * Creates StructureObject or PageObject by ID (see table lm_data)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMObjectFactory
{
    public static function getInstance(
        ilObjLearningModule $a_content_obj,
        int $a_id = 0,
        bool $a_halt = true
    ) : ?ilLMObject {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        $obj = null;
        switch ($obj_rec["type"]) {
            case "st":
                $obj = new ilStructureObject($a_content_obj);
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "pg":
                $obj = new ilLMPageObject($a_content_obj, 0, $a_halt);
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;
        }
        return $obj;
    }
}
