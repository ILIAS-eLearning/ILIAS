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

namespace ILIAS\LearningSequence\Content\Adaptive;

use ilDBInterface;

/**
 * Repository for the path a learner has actually walked through a learning
 * sequence in adaptive mode.
 *
 * The path is stored as an ordered stack of visited ref_ids per learner and
 * learning sequence in the table lso_item_path. The current object is the
 * element with the highest position.
 */
class LSOItemPath
{
    public const string TABLE_NAME = 'lso_item_path';

    /**
     * Database connection.
     */
    protected ilDBInterface $db;

    /**
     * Creates the item path repository.
     */
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Appends a ref_id to the end of the path (push onto the stack).
     */
    public function push(int $usr_id, int $lso_obj_id, int $ref_id): void
    {
        $next_position = $this->getNextPosition($usr_id, $lso_obj_id);
        $this->db->insert(self::TABLE_NAME, [
            'usr_id' => ['integer', $usr_id],
            'lso_obj_id' => ['integer', $lso_obj_id],
            'position' => ['integer', $next_position],
            'ref_id' => ['integer', $ref_id]
        ]);
    }

    /**
     * Removes the last element of the path (pop from the stack) and returns
     * the ref_id that was removed, or null if the path was empty.
     */
    public function pop(int $usr_id, int $lso_obj_id): ?int
    {
        $top_position = $this->getTopPosition($usr_id, $lso_obj_id);
        if ($top_position === null) {
            return null;
        }

        $query = "SELECT ref_id FROM " . self::TABLE_NAME
            . " WHERE usr_id = " . $this->db->quote($usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($lso_obj_id, 'integer')
            . " AND position = " . $this->db->quote($top_position, 'integer');
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        $ref_id = $row ? (int) $row['ref_id'] : null;

        $this->db->manipulate(
            "DELETE FROM " . self::TABLE_NAME
            . " WHERE usr_id = " . $this->db->quote($usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($lso_obj_id, 'integer')
            . " AND position = " . $this->db->quote($top_position, 'integer')
        );

        return $ref_id;
    }

    /**
     * Returns the ordered list of visited ref_ids (oldest first).
     *
     * @return int[]
     */
    public function getPath(int $usr_id, int $lso_obj_id): array
    {
        $query = "SELECT ref_id FROM " . self::TABLE_NAME
            . " WHERE usr_id = " . $this->db->quote($usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($lso_obj_id, 'integer')
            . " ORDER BY position ASC";
        $res = $this->db->query($query);

        $path = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $path[] = (int) $row['ref_id'];
        }
        return $path;
    }

    /**
     * Returns the current ref_id (element with the highest position), or null
     * if the path is empty.
     */
    public function getCurrent(int $usr_id, int $lso_obj_id): ?int
    {
        $top_position = $this->getTopPosition($usr_id, $lso_obj_id);
        if ($top_position === null) {
            return null;
        }

        $query = "SELECT ref_id FROM " . self::TABLE_NAME
            . " WHERE usr_id = " . $this->db->quote($usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($lso_obj_id, 'integer')
            . " AND position = " . $this->db->quote($top_position, 'integer');
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        return $row ? (int) $row['ref_id'] : null;
    }

    /**
     * Removes the whole path for a learner and learning sequence.
     */
    public function reset(int $usr_id, int $lso_obj_id): void
    {
        $this->db->manipulate(
            "DELETE FROM " . self::TABLE_NAME
            . " WHERE usr_id = " . $this->db->quote($usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($lso_obj_id, 'integer')
        );
    }

    /**
     * Removes all path entries pointing to a given object ref_id within a
     * learning sequence (for all learners). Used when an object is removed
     * from the LSO so that no orphaned entries remain.
     */
    public function deleteForItemRefId(int $lso_obj_id, int $ref_id): void
    {
        $this->db->manipulate(
            "DELETE FROM " . self::TABLE_NAME
            . " WHERE lso_obj_id = " . $this->db->quote($lso_obj_id, 'integer')
            . " AND ref_id = " . $this->db->quote($ref_id, 'integer')
        );
    }

    /**
     * Returns the highest position currently stored, or null if the path is
     * empty.
     */
    protected function getTopPosition(int $usr_id, int $lso_obj_id): ?int
    {
        $query = "SELECT MAX(position) AS max_position FROM " . self::TABLE_NAME
            . " WHERE usr_id = " . $this->db->quote($usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($lso_obj_id, 'integer');
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        if (!$row || $row['max_position'] === null) {
            return null;
        }
        return (int) $row['max_position'];
    }

    /**
     * Returns the next free position (0 if the path is empty).
     */
    protected function getNextPosition(int $usr_id, int $lso_obj_id): int
    {
        $top_position = $this->getTopPosition($usr_id, $lso_obj_id);
        return $top_position === null ? 0 : $top_position + 1;
    }
}
