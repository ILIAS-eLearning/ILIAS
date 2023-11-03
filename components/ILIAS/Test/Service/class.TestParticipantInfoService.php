<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Test;
class TestParticipantInfoService
{
    private \ilDBInterface $database;

    public function __construct(\ilDBInterface $db)
    {
        $this->database = $db;
    }

    public function lookupTestIdByActiveId(int $active_id): int
    {
        $result = $this->database->queryF(
            "SELECT test_fi FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        $test_id = -1;
        if ($this->database->numRows($result) > 0) {
            $row = $this->database->fetchAssoc($result);
            $test_id = (int) $row["test_fi"];
        }

        return $test_id;
    }
}
