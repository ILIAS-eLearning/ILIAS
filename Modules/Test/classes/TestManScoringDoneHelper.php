<?php declare(strict_types=1);
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

namespace ILIAS\Test;

use ilDBInterface;

/**
 * Class TestManScoringDoneHelper
 * @package ILIAS\Test
 * @author  Marvin Beym <mbeym@databay.de>
 */
class TestManScoringDoneHelper
{
    private ?ilDBInterface $db;
    private const TABLE_NAME = "manscoring_done";

    public function __construct(?ilDBInterface $db = null)
    {
        if (!$db) {
            global $DIC;
            $db = $DIC->database();
        }
        $this->db = $db;
    }

    public function isDone(int $activeId) : bool
    {
        $result = $this->db->queryF(
            "SELECT done FROM " . self::TABLE_NAME . " WHERE active_id = %s AND done = 1",
            ["integer"],
            [$activeId]
        );

        return $result->numRows() === 1;
    }

    public function exists(int $activeId) : bool
    {
        $result = $this->db->queryF(
            "SELECT active_id FROM " . self::TABLE_NAME . " WHERE active_id = %s",
            ["integer"],
            [$activeId]
        );

        return $result->numRows() === 1;
    }

    public function setDone(int $activeId, bool $done) : void
    {
        if ($this->exists($activeId)) {
            $this->db->manipulateF("UPDATE " . self::TABLE_NAME . " SET done = %s WHERE active_id = %s",
                ["integer", "integer"],
                [$done, $activeId]
            );
            return;
        }

        $this->db->manipulateF("INSERT INTO " . self::TABLE_NAME . " (active_id, done) VALUES (%s, %s)",
            ["integer", "integer"],
            [$activeId, $done]
        );
    }
}