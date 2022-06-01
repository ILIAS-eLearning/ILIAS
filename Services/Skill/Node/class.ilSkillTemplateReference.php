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
 ********************************************************************
 */

/**
 * Skill Template Reference
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTemplateReference extends ilSkillTreeNode
{
    protected ilDBInterface $db;
    protected int $skill_template_id = 0;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct($a_id);
        $this->setType("sktr");
    }

    public function setSkillTemplateId(int $a_val) : void
    {
        $this->skill_template_id = $a_val;
    }

    public function getSkillTemplateId() : int
    {
        return $this->skill_template_id;
    }

    public function read() : void
    {
        $ilDB = $this->db;
        
        parent::read();
        
        $set = $ilDB->query(
            "SELECT * FROM skl_templ_ref " .
            " WHERE skl_node_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->setSkillTemplateId((int) $rec["templ_id"]);
    }

    public function create() : void
    {
        $ilDB = $this->db;
        
        parent::create();
        
        $ilDB->manipulate("INSERT INTO skl_templ_ref " .
            "(skl_node_id, templ_id) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getSkillTemplateId(), "integer") .
            ")");
    }

    public function update() : void
    {
        $ilDB = $this->db;
        
        parent::update();
        
        $ilDB->manipulate(
            "UPDATE skl_templ_ref SET " .
            " templ_id = " . $ilDB->quote($this->getSkillTemplateId(), "integer") .
            " WHERE skl_node_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    public function delete() : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_templ_ref WHERE "
            . " skl_node_id = " . $ilDB->quote($this->getId(), "integer")
        );

        parent::delete();
    }

    public function copy() : ilSkillTemplateReference
    {
        $sktr = new ilSkillTemplateReference();
        $sktr->setTitle($this->getTitle());
        $sktr->setDescription($this->getDescription());
        $sktr->setType($this->getType());
        $sktr->setSkillTemplateId($this->getSkillTemplateId());
        $sktr->setSelfEvaluation($this->getSelfEvaluation());
        $sktr->setOrderNr($this->getOrderNr());
        $sktr->create();

        return $sktr;
    }

    public static function _lookupTemplateId(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT templ_id FROM skl_templ_ref WHERE skl_node_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return (int) $obj_rec["templ_id"];
    }

    /**
     * @param int $a_template_id (top) template node id
     * @return array|int[]
     */
    public static function _lookupTrefIdsForTopTemplateId(int $a_template_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_templ_ref " .
            " WHERE templ_id = " . $ilDB->quote($a_template_id, "integer")
        );
        $trefs = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $trefs[] = (int) $rec["skl_node_id"];
        }
        return $trefs;
    }


    /**
     * @param int $a_tid template node id (node id in template tree)
     * @return array|int[]
     */
    public static function _lookupTrefIdsForTemplateId(int $a_tid) : array
    {
        global $DIC;

        $tree = $DIC->skills()->internal()->repo()->getTreeRepo()->getTreeForNodeId($a_tid);
        $top_template_id = $tree->getTopParentNodeId($a_tid);
        return self::_lookupTrefIdsForTopTemplateId($top_template_id);
    }
}
