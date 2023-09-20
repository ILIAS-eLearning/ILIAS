<?php declare(strict_types=1);

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
