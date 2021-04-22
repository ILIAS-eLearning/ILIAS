<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecords
{
    /**
     * @var ilDBInterface
     */
    private $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * @param int    $obj_id
     * @param string $sub_type
     * @return ilAdvancedMDRecord[]
     */
    public function getActiveRecordsByObjId(int $obj_id, string $sub_type = '') : array
    {
    }
}
