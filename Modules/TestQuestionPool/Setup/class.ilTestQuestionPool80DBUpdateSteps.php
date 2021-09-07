<?php declare(strict_types=1);

class ilTestQuestionPool80DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1(ilDBInterface $db) : void
    {
        $db->manipulateF("DELETE FROM qpl_qst_type WHERE type_tag = %s", ['text'], ['assJavaApplet']);
        $db->manipulateF("DELETE FROM qpl_qst_type WHERE type_tag = %s", ['text'], ['assFlashQuestion']);
    }
}
