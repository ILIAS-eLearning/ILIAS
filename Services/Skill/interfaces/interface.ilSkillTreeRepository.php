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

use ILIAS\Skill\Tree;

/**
 * Interface ilSkillTreeRepository
 */
interface ilSkillTreeRepository
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

    public function isInAnyTree(int $node_id) : bool;

    public function getTreeIdForNodeId(int $node_id) : int;

    public function getTreeForNodeId(int $node_id) : ilSkillTree;

    public function getVirtualTreeForNodeId(int $node_id) : ilVirtualSkillTree;
}
