<?php declare(strict_types=1);

class ilTestQuestionPool80DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        $this->db->manipulateF("DELETE FROM qpl_qst_type WHERE type_tag = %s", ['text'], ['assJavaApplet']);
        $this->db->manipulateF("DELETE FROM qpl_qst_type WHERE type_tag = %s", ['text'], ['assFlashQuestion']);
    }
    
    public function step_2() : void
    {
        if (!$this->db->tableColumnExists('tst_rnd_quest_set_qpls', 'pool_ref_id')) {
            $this->db->addTableColumn(
                'tst_rnd_quest_set_qpls',
                'pool_ref_id',
                [
                    'type' => ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => false,
                    'default' => null
                ]
            );
        }
    }

    public function step_3() : void
    {
        $fields = ['gap_id'];
        if (!$this->db->indexExistsByFields('qpl_a_cloze', $fields)) {
            $this->db->addIndex(
                'qpl_a_cloze',
                $fields,
                'i2'
            );
        }
    }

    public function step_4() : void
    {
        $fields = ['gap_fi', 'question_fi'];
        if (!$this->db->indexExistsByFields('qpl_a_cloze_combi_res', $fields)) {
            $this->db->addIndex(
                'qpl_a_cloze_combi_res',
                $fields,
                'i1'
            );
        }
    }
}
