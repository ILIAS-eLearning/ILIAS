<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use \ILIAS\Skill\Tree;

/**
 * Interface ilBasicSkillTreeRepository
 */
interface ilBasicSkillTreeRepository
{

    /**
     * Get common skill ids for import IDs (newest first)
     * @param int         $a_source_inst_id  source installation id, must be <>0
     * @param int         $a_skill_import_id source skill id (type basic skill ("skll") or basic skill template ("sktp"))
     * @param int         $a_tref_import_id  source template reference id (if > 0 skill_import_id will be of type "sktp")
     * @return array array of common skill ids, keys are "skill_id", "tref_id", "creation_date"
     */
    public function getCommonSkillIdForImportId(
        int $a_source_inst_id,
        int $a_skill_import_id,
        int $a_tref_import_id = 0
    ) : array;

    /**
     * Get level ids for import IDs (newest first)
     * @param int $a_source_inst_id  source installation id, must be <>0
     * @param int $a_level_import_id source level id
     * @return array array of common skill ids, keys are "level_id", "creation_date"
     */
    public function getLevelIdForImportId(int $a_source_inst_id, int $a_level_import_id) : array;

    /**
     * Check if node id is in any tree
     * @param int $node_id
     * @return bool
     */
    public function isInAnyTree(int $node_id) : bool;

    /**
     * @param int $node_id
     * @return int
     */
    public function getTreeIdForNodeId(int $node_id) : int;

    /**
     * Get tree for node id
     * @param int $node_id
     * @return ilSkillTree
     */
    public function getTreeForNodeId(int $node_id) : ilSkillTree;

    /**
     * Get virtual tree for node id
     * @param int $node_id
     * @return ilVirtualSkillTree
     */
    public function getVirtualTreeForNodeId(int $node_id) : ilVirtualSkillTree;

}