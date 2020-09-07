<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilBasicSkill.php");
include_once("./Services/Skill/classes/class.ilSkillCategory.php");
include_once("./Services/Skill/classes/class.ilSkillRoot.php");
include_once("./Services/Skill/classes/class.ilBasicSkillTemplate.php");
include_once("./Services/Skill/classes/class.ilSkillTemplateCategory.php");
include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");

/**
 * Factory for skill tree nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillTreeNodeFactory
{
    public static function getInstance($a_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $obj = null;

        switch ($obj_rec["type"]) {
            case "skll":
                $obj = new ilBasicSkill();
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "scat":
                $obj = new ilSkillCategory();
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "sktp":
                $obj = new ilBasicSkillTemplate();
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "sctp":
                $obj = new ilSkillTemplateCategory();
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "skrt":
                $obj = new ilSkillRoot();
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;

            case "sktr":
                $obj = new ilSkillTemplateReference();
                $obj->setId($obj_rec["obj_id"]);
                $obj->setDataRecord($obj_rec);
                $obj->read();
                break;
        }
        return $obj;
    }
}
