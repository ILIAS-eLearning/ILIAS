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

namespace ILIAS\Tracking\DB\LPCollectionManual;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Tracking\DB\LPCollectionManual\Element\FactoryInterface as ElementFactoryInterface;
use ILIAS\Tracking\DB\LPCollectionManual\Element\LPCollectionManualEntryInterface;
use ILIAS\Tracking\DB\LPCollectionManual\Element\LPCollectionManualInterface;

class Repository implements RepositoryInterface
{
    public function __construct(
        protected ilDBInterface $db,
        protected ElementFactoryInterface $element_factory
    ) {
    }

    public function readEntriesOfObject(
        int $object_id
    ): LPCollectionManualInterface {
        $query = "SELECT * FROM ut_lp_coll_manual "
            . "WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $elements = [];
        while ($row = $res->fetchAssoc()) {
            $elements[] = $this->entryFromRowData($row);
        };
        return $this->element_factory->lpCollectionManual(...$elements);
    }

    public function readEntryForUserOfSubitemOfObject(
        int $object_id,
        int $user_id,
        int $subitem_id
    ): LPCollectionManualEntryInterface|null {
        $query = "SELECT * FROM ut_lp_coll_manual "
            . "WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER) . " "
            . "AND usr_id = " . $this->db->quote($user_id, ilDBConstants::T_INTEGER) . " "
            . "AND subitem_id = " . $this->db->quote($subitem_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $row = $res->fetchAssoc();
        return is_null($row) ? null : $this->entryFromRowData($row);
    }

    public function write(
        LPCollectionManualEntryInterface $entry
    ): void {
        $this->writeCollection($this->element_factory->lpCollectionManual($entry));
    }

    public function writeCollection(
        LPCollectionManualInterface $collection_manual
    ): void {
        if (count($collection_manual) === 0) {
            return;
        }
        $tuples = [];
        foreach ($collection_manual as $collection_manual_entry) {
            $tuple = "("
                . $this->db->quote($collection_manual_entry->getObjectId(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote($collection_manual_entry->getUserId(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote($collection_manual_entry->getSubitemId(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote((int) $collection_manual_entry->isCompleted(), ilDBConstants::T_INTEGER) . ", "
                . $this->db->quote($collection_manual_entry->getLastChanged(), ilDBConstants::T_INTEGER) . ")";
            $tuples[] = $tuple;
        }
        $query = "INSERT INTO ut_lp_coll_manual (obj_id, usr_id, subitem_id, completed, last_change)"
            . " VALUES " . implode(", ", $tuples)
            . " ON DUPLICATE KEY UPDATE completed=VALUES(completed), last_change=VALUES(last_change)";
        $this->db->manipulate($query);
    }

    public function deleteEntriesOfObject(
        int $object_id
    ): void {
        $query = "DELETE FROM ut_lp_coll_manual" .
            " WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        ;
        $this->db->manipulate($query);
    }

    protected function entryFromRowData(
        array $row
    ): LPCollectionManualEntryInterface {
        return $this->element_factory->lpCollectionManualEntry()
            ->withObjectId((int) $row['obj_id'])
            ->withUserId((int) $row['usr_id'])
            ->withSubitemId((int) $row['subitem_id'])
            ->withLastChanged((int) $row['last_changed'])
            ->withCompletedStatus((bool) ((int) $row['completed']));
    }
}
