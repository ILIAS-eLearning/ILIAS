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

namespace ILIAS\Tracking\DB\LPMarks;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Tracking\DB\LPMarks\Element\FactoryInterface as ElementFactoryInterface;
use ILIAS\Tracking\DB\LPMarks\Element\LPMarkCollectionInterface;
use ILIAS\Tracking\DB\LPMarks\Element\LPMarkInterface;

class Repository implements RepositoryInterface
{
    public function __construct(
        protected ilDBInterface $db,
        protected ElementFactoryInterface $element_factory
    ) {
    }

    public function write(
        LPMarkInterface $lp_mark
    ): int {
        $query = "INSERT INTO ut_lp_marks (obj_id, usr_id, completed, mark, u_comment, status, status_changed, status_dirty, percentage) VALUES ("
            . $this->db->quote($lp_mark->getObjectId(), ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($lp_mark->getUserId(), ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote((int) $lp_mark->isCompleted(), ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($lp_mark->getMark(), ilDBConstants::T_TEXT) . ", "
            . $this->db->quote($lp_mark->getComment(), ilDBConstants::T_TEXT) . ", "
            . $this->db->quote($lp_mark->getStatus(), ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($lp_mark->getStatusChanged(), ilDBConstants::T_DATETIME) . ", "
            . $this->db->quote($lp_mark->getStatusDirty(), ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($lp_mark->getPercentage(), ilDBConstants::T_INTEGER) . ")"
            . " ON DUPLICATE KEY UPDATE completed=VALUES(completed), mark=VALUES(mark), u_comment=VALUES(u_comment), status=VALUES(status), status_changed=VALUES(status_changed), status_dirty=VALUES(status_dirty), percentage=VALUES(percentage)";
        return $this->db->manipulate($query);
    }

    public function writeCollection(
        LPMarkCollectionInterface $lp_mark_collection
    ): void {
        if (count($lp_mark_collection) === 0) {
            return;
        }
        $tuples = [];
        foreach ($lp_mark_collection as $lp_mark) {
            $tuple = "("
                . $this->db->quote($lp_mark->getObjectId(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote($lp_mark->getUserId(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote((int) $lp_mark->isCompleted(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote($lp_mark->getMark(), ilDBConstants::T_TEXT) . ", "
                . $this->db->quote($lp_mark->getComment(), ilDBConstants::T_TEXT) . ", "
                . $this->db->quote($lp_mark->getStatus(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote($lp_mark->getStatusChanged(), ilDBConstants::T_DATETIME) . ", "
                . $this->db->quote($lp_mark->getStatusDirty(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote($lp_mark->getPercentage(), ilDBConstants::T_INTEGER) . ")";
            $tuples[] = $tuple;
        }
        $query = "INSERT INTO ut_lp_collections (obj_id, usr_id, completed, mark, u_comment, status, status_changed, status_dirty, percentage)"
            . " VALUES " . implode(", ", $tuples)
            . " ON DUPLICATE KEY UPDATE completed=VALUES(completed), mark=VALUES(mark), u_comment=VALUES(u_comment), status=VALUES(status), status_changed=VALUES(status_changed), status_dirty=VALUES(status_dirty), percentage=VALUES(percentage)";
        $this->db->manipulate($query);
    }

    public function readAllEntriesOfObject(
        int $object_id
    ): LPMarkCollectionInterface {
        $query = "SELECT * FROM ut_lp_marks WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $elements = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $elements[] = $this->lpMarkFromRow($row);
        }
        return $this->element_factory->lpMarkCollection(...$elements);
    }

    public function readAllEntriesWithStatusChangedAfter(
        string $timestamp
    ): LPMarkCollectionInterface {
        $query = "SELECT * FROM ut_lp_marks " .
            " WHERE status_changed >= " . $this->db->quote($timestamp, ilDBConstants::T_TIMESTAMP);
        $res = $this->db->query($query);
        $elements = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $elements[] = $this->lpMarkFromRow($row);
        }
        return $this->element_factory->lpMarkCollection(...$elements);
    }

    public function readAllEntriesWithStatusOfObject(
        int $object_id,
        int $status
    ): LPMarkCollectionInterface {
        $query = "SELECT * FROM ut_lp_marks "
            . "WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER) . " "
            . "AND status = " . $this->db->quote($status, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $elements = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $elements[] = $this->lpMarkFromRow($row);
        }
        return $this->element_factory->lpMarkCollection(...$elements);
    }

    public function readEntriesForUserOfObjects(
        int $user_id,
        int ...$object_ids
    ): LPMarkCollectionInterface {
        if (count($object_ids) === 0) {
            return $this->element_factory->lpMarkCollection();
        }
        $query = "SELECT * FROM ut_lp_marks" .
            " WHERE " . $this->db->in("obj_id", $object_ids, false, ilDBConstants::T_INTEGER) .
            " AND usr_id = " . $this->db->quote($user_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $elements = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $elements[] = $this->lpMarkFromRow($row);
        }
        return $this->element_factory->lpMarkCollection(...$elements);
    }

    public function readEntryForUserOfObject(
        int $object_id,
        int $user_id
    ): LPMarkInterface|null {
        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $this->db->quote($user_id, ilDBConstants::T_INTEGER) . " " .
            "AND obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $row = $res->fetchAssoc();
        if (is_null($row)) {
            return null;
        }
        return $this->lpMarkFromRow($row);
    }

    public function readByUserIdAndStatusAndTimeInterval(
        int $user_id,
        int $status,
        string $from,
        string $to
    ): LPMarkCollectionInterface {
        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $this->db->quote($user_id, ilDBConstants::T_INTEGER) .
            " AND status = " . $this->db->quote($status, ilDBConstants::T_INTEGER) .
            " AND status_changed >= " . $this->db->quote($from, ilDBConstants::T_TIMESTAMP) .
            " AND status_changed <= " . $this->db->quote($to, ilDBConstants::T_TIMESTAMP);
        $res = $this->db->query($query);
        $elements = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $elements[] = $this->lpMarkFromRow($row);
        }
        return $this->element_factory->lpMarkCollection(...$elements);
    }

    public function delete(
        int $object_id
    ): void {
        $query = "DELETE FROM ut_lp_marks WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    public function deleteByUserId(
        int $object_id,
        int $user_id
    ): void {
        $this->deleteByUserIds(
            $object_id,
            $user_id
        );
    }

    public function deleteByUserIds(
        int $object_id,
        int ...$user_ids
    ): void {
        if (count($user_ids) === 0) {
            return;
        }
        $query = "DELETE FROM ut_lp_marks" .
            " WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER) .
            " AND " . $this->db->in("usr_id", $user_ids, false, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    public function markAllRowsAsDirty(): void
    {
        $query = "UPDATE ut_lp_marks SET status_dirty = " . $this->db->quote(1, ilDBConstants::T_INTEGER);
        ;
        $this->db->manipulate($query);
    }

    protected function lpMarkFromRow(array $row): LPMarkInterface
    {
        return $this->element_factory->lpMark()
            ->withObjectId((int) $row['obj_id'])
            ->withUserId((int) $row['usr_id'])
            ->withCompletedStatus((bool) ((int) $row['completed']))
            ->withMark($row['mark'])
            ->withComment($row['u_comment'])
            ->withStatus((int) $row['status'])
            ->withStatusChanged((string) ($row['status_changed'] ?? ''))
            ->withStatusDirty((int) $row['status_dirty'])
            ->withPercentage((int) $row['percentage']);
    }
}
