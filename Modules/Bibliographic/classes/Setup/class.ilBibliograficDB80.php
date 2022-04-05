<?php

/**
 * Class ilBibliograficDB80
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilBibliograficDB80 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $database;

    /**
     * @inheritDoc
     */
    public function prepare(ilDBInterface $db)
    {
        $this->database = $db;
    }

    public function step_1() : void
    {
        if ($this->database->tableColumnExists('il_bibl_field', 'object_id')) {
            $this->database->dropTableColumn('il_bibl_field', 'object_id');
        }
    }
}
