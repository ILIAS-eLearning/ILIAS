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

namespace ILIAS\Tracking\DB\LPCollection;

use ilDBConstants;
use ilDBInterface;
use ilDBStatement;
use ILIAS\Tracking\DB\LPCollection\Element\FactoryInterface as ElementFactoryInterface;
use ILIAS\Tracking\DB\LPCollection\Element\LPCollectionInterface;

class Repository implements RepositoryInterface
{
    public function __construct(
        protected ilDBInterface $db,
        protected ElementFactoryInterface $element_factory
    ) {
    }

    public function readLPCollection(
        int $object_id
    ): LPCollectionInterface|null {
        $query = "SELECT * FROM ut_lp_collections WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        return $this->buildCollectionWithQueryResult($object_id, $res);
    }

    public function readLPCollectionWithReferenceInObjectReference(
        int $object_id
    ): LPCollectionInterface|null {
        $query = "SELECT ut_lp_collections.obj_id, ut_lp_collections.item_id, ut_lp_collections.grouping_id, ut_lp_collections.lpmode, ut_lp_collections.num_obligatory, ut_lp_collections.active FROM object_reference "
            . "JOIN ut_lp_collections "
            . "ON (object_reference.obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER) . " "
            . "AND object_reference.ref_id = ut_lp_collections.item_id)";
        $res = $this->db->query($query);
        return $this->buildCollectionWithQueryResult($object_id, $res);
    }

    public function writeLPCollection(
        LPCollectionInterface $lp_collection
    ): void {
        if (count($lp_collection) === 0) {
            return;
        }
        $tuples = [];
        foreach ($lp_collection as $lp_collection_element) {
            $tuple = "(";
            $tuple .= $this->db->quote($lp_collection->getObjectId(), ilDBConstants::T_INTEGER) . ", ";
            $tuple .= $this->db->quote($lp_collection_element->getItemId(), ilDBConstants::T_INTEGER) . ", ";
            $tuple .= $this->db->quote($lp_collection_element->getGroupingId(), ilDBConstants::T_INTEGER) . ", ";
            $tuple .= $this->db->quote($lp_collection_element->getNumObligatory(), ilDBConstants::T_INTEGER) . ", ";
            $tuple .= $this->db->quote((int) $lp_collection_element->isActive(), ilDBConstants::T_INTEGER) . ", ";
            $tuple .= $this->db->quote($lp_collection_element->getLpMode(), ilDBConstants::T_INTEGER) . ")";
            $tuples[] = $tuple;
        }
        $query = "INSERT INTO ut_lp_collections (obj_id, item_id, grouping_id, num_obligatory, active, lpmode)"
            . " VALUES " . implode(", ", $tuples)
            . " ON DUPLICATE KEY UPDATE item_id = VALUES(item_id), grouping_id = VALUES(grouping_id), num_obligatory = VALUES(num_obligatory), active = VALUES(active), lpmode = VALUES(lpmode)";
        $this->db->manipulate($query);
    }


    public function deleteLPCollection(
        int $object_id
    ): void {
        $query = "DELETE FROM ut_lp_collections WHERE obj_id = " . $this->db->quote($object_id, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    public function deleteLPCollectionEntry(
        int $object_id,
        int $item_id
    ): void {
        $query = "DELETE FROM ut_lp_collections" .
            " WHERE obj_id = " . $this->db->quote($object_id, "integer") .
            " AND item_id = " . $this->db->quote($item_id, "integer");
        $this->db->manipulate($query);
    }

    public function deleteLPCollectionEntryByGroupingId(
        int $object_id,
        int $item_id,
        int $grouping_id
    ): void {
        $query = "DELETE FROM ut_lp_collections " .
            " WHERE obj_id = " . $this->db->quote($object_id, "integer") .
            " AND item_id = " . $this->db->quote($item_id, "integer") .
            " AND grouping_id = " . $this->db->quote($grouping_id, "integer");
        $this->db->manipulate($query);
    }

    public function deleteLPCollectionManual(
        int $object_id
    ): void {
        $query = "DELETE FROM ut_lp_coll_manual" .
            " WHERE obj_id = " . $this->db->quote($object_id, "integer");
        $this->db->manipulate($query);
    }

    protected function buildCollectionWithQueryResult(
        int $object_id,
        ilDBStatement $res
    ): LPCollectionInterface|null {
        $elements = [];
        while ($row = $res->fetchAssoc()) {
            $lp_collection_element = $this->element_factory->lpCollectionElement()
                ->withItemId((int) $row['item_id'])
                ->withGroupingId((int) $row['grouping_id'])
                ->withLPMode((int) $row['lpmode'])
                ->withNumObligatory((int) $row['num_obligatory'])
                ->withIsActive((bool) $row['active']);
            $elements[] = $lp_collection_element;
        }
        return count($elements) === 0
            ? null
            : $this->element_factory->lpCollection(...$elements)->withObjectId($object_id);
    }
}
