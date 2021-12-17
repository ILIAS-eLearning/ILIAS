<?php declare(strict_types = 1);

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

class ilSkillDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function step_1()
    {
        if ($this->db->sequenceExists('skl_self_eval')) {
            $this->db->dropSequence('skl_self_eval');
        }

        if ($this->db->tableExists('skl_self_eval')) {
            $this->db->dropTable('skl_self_eval');
        }

        if ($this->db->tableExists('skl_self_eval_level')) {
            $this->db->dropTable('skl_self_eval_level');
        }
    }

    public function step_2()
    {
        if (!$this->db->tableColumnExists('skl_user_skill_level', 'trigger_user_id')) {
            $this->db->addTableColumn(
                'skl_user_skill_level',
                'trigger_user_id',
                array(
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 20,
                    'default' => "-"
                )
            );
        }
    }

    public function step_3()
    {
        if (!$this->db->tableColumnExists('skl_user_has_level', 'trigger_user_id')) {
            $this->db->addTableColumn(
                'skl_user_has_level',
                'trigger_user_id',
                array(
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 20,
                    'default' => "-"
                )
            );
        }
    }
}
