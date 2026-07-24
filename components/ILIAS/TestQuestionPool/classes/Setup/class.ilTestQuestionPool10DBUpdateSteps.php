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
        $this->db->manipulateF('UPDATE qpl_questions SET title=%s WHERE ISNULL(title)', [ilDBConstants::T_TEXT], ['']);
        $this->db->manipulateF('UPDATE qpl_questions SET description=%s WHERE ISNULL(description)', [ilDBConstants::T_TEXT], ['']);
        $this->db->manipulateF('UPDATE qpl_questions SET question_text=%s WHERE ISNULL(question_text)', [ilDBConstants::T_TEXT], ['']);
        $this->db->manipulateF('UPDATE qpl_questions SET lifecycle=%s WHERE ISNULL(lifecycle)', [ilDBConstants::T_TEXT], ['draft']);
        $this->db->manipulateF('UPDATE qpl_questions SET complete=%s WHERE ISNULL(complete)', [ilDBConstants::T_TEXT], ['1']);
        $this->db->modifyTableColumn('qpl_questions', 'title', ['notnull' => 1, 'default' => '']);
        $this->db->modifyTableColumn('qpl_questions', 'description', ['notnull' => 1, 'default' => '']);
        $this->db->modifyTableColumn('qpl_questions', 'question_text', ['notnull' => 1, 'default' => '']);
        $this->db->modifyTableColumn('qpl_questions', 'lifecycle', ['notnull' => 1, 'default' => 'draft']);
        $this->db->modifyTableColumn('qpl_questions', 'complete', ['notnull' => 1, 'default' => '1']);
    }

    public function step_2(): void
    {
        if ($this->db->tableColumnExists('qpl_questionpool', 'show_taxonomies')) {
            $this->db->dropTableColumn('qpl_questionpool', 'show_taxonomies');
        }
    }

    /**
     * Composite index supporting the paginated question browser query
     * (ilAssQuestionList two-phase loading, phase A) which filters by
     * obj_fi (eq / IN) and original_id IS NULL and commonly orders by
     * title. Covers both the pool-internal view (obj_fi = X) and the
     * "add from pool" browser (obj_fi IN (...)) of ilObjTestGUI.
     *
     * Note: ILIAS' ilDBPdoFieldDefinition::checkIndexName limits index
     * names to 3 characters, hence the short name "i6" (the existing
     * i1_idx..i5_idx on this table predate that constraint).
     */
    public function step_3(): void
    {
        $fields = ['obj_fi', 'original_id', 'title'];
        if (!$this->db->indexExistsByFields('qpl_questions', $fields)) {
            $this->db->addIndex('qpl_questions', $fields, 'i6');
        }
    }
}
