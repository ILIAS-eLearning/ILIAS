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
 * Stores start and end object boundaries of adaptive learning sequences.
 */
class LSOAdaptiveBoundaries
{
    public const string TABLE_NAME = 'lso_item_boundaries';

    /**
     * Database connection.
     */
    protected ilDBInterface $db;

    /**
     * Creates the boundary repository.
     */
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Gets the start and end boundaries of a learning sequence.
     *
     * @return array{start_ref_id: int, end_ref_id: int}
     */
    public function getBoundariesFor(int $lso_obj_id): array
    {
        $query = "SELECT start_ref_id, end_ref_id FROM " . self::TABLE_NAME . " WHERE obj_id = " . $this->db->quote($lso_obj_id, 'integer');
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        if ($row) {
            return [
                'start_ref_id' => (int) ($row['start_ref_id'] ?? 0),
                'end_ref_id' => (int) ($row['end_ref_id'] ?? 0)
            ];
        }
        return ['start_ref_id' => 0, 'end_ref_id' => 0];
    }

    /**
     * Sets the start object reference ID of a learning sequence.
     */
    public function setStartRefId(int $lso_obj_id, int $start_ref_id): void
    {
        $boundaries = $this->getBoundariesFor($lso_obj_id);
        if ($boundaries['start_ref_id'] === 0 && $boundaries['end_ref_id'] === 0) {
            $this->db->insert(self::TABLE_NAME, [
                'obj_id' => ['integer', $lso_obj_id],
                'start_ref_id' => ['integer', $start_ref_id],
                'end_ref_id' => ['integer', null]
            ]);
        } else {
            $this->db->update(
                self::TABLE_NAME,
                ['start_ref_id' => ['integer', $start_ref_id]],
                ['obj_id' => ['integer', $lso_obj_id]]
            );
        }
    }

    /**
     * Sets the end object reference ID of a learning sequence.
     */
    public function setEndRefId(int $lso_obj_id, int $end_ref_id): void
    {
        $boundaries = $this->getBoundariesFor($lso_obj_id);
        if ($boundaries['start_ref_id'] === 0 && $boundaries['end_ref_id'] === 0) {
            $this->db->insert(self::TABLE_NAME, [
                'obj_id' => ['integer', $lso_obj_id],
                'start_ref_id' => ['integer', null],
                'end_ref_id' => ['integer', $end_ref_id]
            ]);
        } else {
            $this->db->update(
                self::TABLE_NAME,
                ['end_ref_id' => ['integer', $end_ref_id]],
                ['obj_id' => ['integer', $lso_obj_id]]
            );
        }
    }

    /**
     * Removes the start object reference ID of a learning sequence.
     */
    public function unsetStartRefId(int $lso_obj_id): void
    {
        $this->db->update(
            self::TABLE_NAME,
            ['start_ref_id' => ['integer', null]],
            ['obj_id' => ['integer', $lso_obj_id]]
        );
        $this->cleanupIfEmpty($lso_obj_id);
    }

    /**
     * Removes the end object reference ID of a learning sequence.
     */
    public function unsetEndRefId(int $lso_obj_id): void
    {
        $this->db->update(
            self::TABLE_NAME,
            ['end_ref_id' => ['integer', null]],
            ['obj_id' => ['integer', $lso_obj_id]]
        );
        $this->cleanupIfEmpty($lso_obj_id);
    }

    /**
     * Removes a given ref_id from the boundaries of an LSO if it is currently
     * stored as the start and/or end object.
     *
     * Only the affected field(s) are cleared - not the whole row (unless it
     * becomes empty, in which case {@see self::cleanupIfEmpty()} removes it).
     * This is used when an object is deleted from the learning sequence so that
     * a dangling start/end reference does not remain in the database.
     *
     * @param int $lso_obj_id obj_id of the learning sequence
     * @param int $ref_id     ref_id of the deleted object
     * @return bool           true if a boundary field was actually changed
     */
    public function removeRefIdFromBoundaries(int $lso_obj_id, int $ref_id): bool
    {
        if ($ref_id <= 0) {
            return false;
        }

        $boundaries = $this->getBoundariesFor($lso_obj_id);
        $changed = false;

        if ($boundaries['start_ref_id'] === $ref_id) {
            $this->unsetStartRefId($lso_obj_id);
            $changed = true;
        }

        if ($boundaries['end_ref_id'] === $ref_id) {
            $this->unsetEndRefId($lso_obj_id);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Removes an empty boundary record.
     */
    protected function cleanupIfEmpty(int $lso_obj_id): void
    {
        $boundaries = $this->getBoundariesFor($lso_obj_id);
        if ($boundaries['start_ref_id'] === 0 && $boundaries['end_ref_id'] === 0) {
            $this->db->manipulate("DELETE FROM " . self::TABLE_NAME . " WHERE obj_id = " . $this->db->quote($lso_obj_id, 'integer'));
        }
    }
}
