<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPageUpdateSteps
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilContentPageUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        $this->db->manipulateF("UPDATE object_data SET offline = %s WHERE type = %s", ['integer', 'text'], [0, 'copa']);
    }
}
