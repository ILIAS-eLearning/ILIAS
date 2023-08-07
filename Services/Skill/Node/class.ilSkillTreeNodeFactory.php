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
 ********************************************************************
 */

/**
 * Factory for skill tree nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTreeNodeFactory
{
    public static function getInstance(int $a_id = 0): ilSkillTreeNode
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $obj_id = (int) $obj_rec["obj_id"];
        $obj = null;

        switch ($obj_rec["type"]) {
            case "skll":
                $obj = new ilBasicSkill();
                $obj->setId($obj_id);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "scat":
                $obj = new ilSkillCategory();
                $obj->setId($obj_id);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "sktp":
                $obj = new ilBasicSkillTemplate();
                $obj->setId($obj_id);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "sctp":
                $obj = new ilSkillTemplateCategory();
                $obj->setId($obj_id);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "skrt":
                $obj = new ilSkillRoot();
                $obj->setId($obj_id);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "sktr":
                $obj = new ilSkillTemplateReference();
                $obj->setId($obj_id);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;
        }
        return $obj;
    }
}
