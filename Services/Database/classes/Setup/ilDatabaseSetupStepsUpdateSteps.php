<?php declare(strict_types = 1);

class ilDatabaseSetupStepsUpdateSteps implements ilDatabaseUpdateSteps
{
    const TABLE_NAME = ilDBStepExecutionDB::TABLE_NAME;
    const FIELD_STARTED = ilDBStepExecutionDB::FIELD_STARTED;
    const FIELD_FINISHED = ilDBStepExecutionDB::FIELD_FINISHED;

    protected \ilDBInterface $db;
    
    public function prepare(\ilDBInterface $db)
    {
        $this->db = $db;
    }
    
    public function step_1(\ilDBInterface $db)
    {
        if ($db->tableExists(self::TABLE_NAME)
        && $db->tableColumnExists(self::TABLE_NAME, self::FIELD_STARTED)
        ) {
            $db->modifyTableColumn(self::TABLE_NAME, self::FIELD_STARTED, [
                'length' => 26,
                'type' => ilDBConstants::T_TEXT,
                'notnull' => false
            ]);
        }
    }
    
    public function step_2(\ilDBInterface $db)
    {
        if ($db->tableExists(self::TABLE_NAME)
        && $db->tableColumnExists(self::TABLE_NAME, self::FIELD_FINISHED)
        ) {
            $db->modifyTableColumn(self::TABLE_NAME, self::FIELD_FINISHED, [
                'length' => 26,
                'type' => ilDBConstants::T_TEXT,
                'notnull' => false
            ]);
        }
    }
}
