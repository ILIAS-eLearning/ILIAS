<?php declare(strict_types = 1);

class ilStudyProgrammeProgressTableUpdateSteps implements ilDatabaseUpdateSteps
{
    const TABLE_NAME = 'prg_usr_progress';

    protected \ilDBInterface $db;
    
    public function prepare(\ilDBInterface $db)
    {
        $this->db = $db;
    }
    
    public function step_1(\ilDBInterface $db)
    {
        $column_name = 'individual';

        if (!$db->tableColumnExists(self::TABLE_NAME, $column_name)) {
            $db->addTableColumn(
                self::TABLE_NAME,
                $column_name,
                [
                    "type" => "integer",
                    "length" => 1,
                    "notnull" => true,
                    "default" => 0
                ]
            );
            $query = 'UPDATE ' . self::TABLE_NAME
                . ' SET ' . $column_name . ' = 1'
                . ' WHERE last_change_by IS NOT NULL';
            $db->manipulate($query);
        }
    }
    
    public function step_2(\ilDBInterface $db)
    {
        $old = "risky_to_fail_mail_send";
        $new = "sent_mail_risky_to_fail";
        $table = "prg_usr_progress";
        if ($db->tableColumnExists(self::TABLE_NAME, $old) && !$db->tableColumnExists(self::TABLE_NAME, $new)) {
            $db->renameTableColumn(self::TABLE_NAME, $old, $new);
        }
    }
    
    public function step_3(\ilDBInterface $db)
    {
        $column_name = 'sent_mail_expires';

        if (!$db->tableColumnExists(self::TABLE_NAME, $column_name)) {
            $db->addTableColumn(
                self::TABLE_NAME,
                $column_name,
                [
                    "type" => "timestamp",
                    "notnull" => false
                ]
            );
        }
    }
}
