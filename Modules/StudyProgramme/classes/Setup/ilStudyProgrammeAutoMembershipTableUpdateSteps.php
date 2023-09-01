<?php

declare(strict_types=1);

class ilStudyProgrammeAutoMembershipTableUpdateSteps implements ilDatabaseUpdateSteps
{
    public const TABLE_NAME = 'prg_auto_membership';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->addTableColumn(
            self::TABLE_NAME,
            'search_recursive',
            [
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => false
            ]
        );
    }
}
