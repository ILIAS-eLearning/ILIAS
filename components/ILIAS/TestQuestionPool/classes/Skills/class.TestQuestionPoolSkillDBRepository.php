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

declare(strict_types=1);

namespace ILIAS\TestQuestionPool\Skills;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class TestQuestionPoolSkillDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db = null
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
    }

    public function removeForSkill(int $skill_node_id, bool $is_reference): void
    {
        if (!$is_reference) {
            $this->db->manipulate("DELETE FROM qpl_qst_skl_assigns " .
                " WHERE skill_base_fi = " . $this->db->quote($skill_node_id, "integer"));
            $this->db->manipulate("DELETE FROM qpl_qst_skl_sol_expr " .
                " WHERE skill_base_fi = " . $this->db->quote($skill_node_id, "integer"));
        } else {
            $this->db->manipulate("DELETE FROM qpl_qst_skl_assigns " .
                " WHERE skill_tref_fi = " . $this->db->quote($skill_node_id, "integer"));
            $this->db->manipulate("DELETE FROM qpl_qst_skl_sol_expr " .
                " WHERE skill_tref_fi = " . $this->db->quote($skill_node_id, "integer"));
        }
    }
}
