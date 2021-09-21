<?php

class ilTestQuestionPool80DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1(\ilDBInterface $db) : void
    {
        $db->manipulate("DELETE FROM qpl_qst_type WHERE type_tag = 'assJavaApplet'");
        $db->manipulate("DELETE FROM qpl_qst_type WHERE type_tag = 'assFlashQuestion'");
    }
}