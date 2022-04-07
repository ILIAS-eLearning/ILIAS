<?php declare(strict_types=1);

class ilTest8DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }
    
    public function step_1() : void
    {
        $this->db->dropTableColumn('tst_tests', 'mc_scoring');
    }
}
