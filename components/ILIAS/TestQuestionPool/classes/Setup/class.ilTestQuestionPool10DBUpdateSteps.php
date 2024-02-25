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

class ilTestQuestionPool10DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulateF('UPDATE qpl_questions SET title=%s WHERE IS NULL title', [ilDBConstants::T_TEXT], ['']);
        $this->db->manipulateF('UPDATE qpl_questions SET description=%s WHERE IS NULL description', [ilDBConstants::T_TEXT], ['']);
        $this->db->manipulateF('UPDATE qpl_questions SET question_text=%s WHERE IS NULL question_text', [ilDBConstants::T_TEXT], ['']);
        $this->db->manipulateF('UPDATE qpl_questions SET lifecycle=%s WHERE IS NULL lifecycle', [ilDBConstants::T_TEXT], ['draft']);
        $this->db->manipulateF('UPDATE qpl_questions SET complete=%s WHERE IS NULL complete', [ilDBConstants::T_TEXT], ['1']);
        $this->db->modifyTableColumn('qpl_questions', 'title', ['notnull' => 1, 'default' => '']);
        $this->db->modifyTableColumn('qpl_questions', 'description', ['notnull' => 1, 'default' => '']);
        $this->db->modifyTableColumn('qpl_questions', 'question_text', ['notnull' => 1, 'default' => '']);
        $this->db->modifyTableColumn('qpl_questions', 'lifecycle', ['notnull' => 1, 'default' => 'draft']);
        $this->db->modifyTableColumn('qpl_questions', 'complete', ['notnull' => 1, 'default' => '1']);
    }
}
