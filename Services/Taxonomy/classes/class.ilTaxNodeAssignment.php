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

/**
 * Taxonomy node <-> item assignment
 * This class allows to assign items to taxonomy nodes.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxNodeAssignment
{
    protected ilDBInterface $db;
    protected string $component_id;
    protected int $taxonomy_id;
    protected string $item_type;
    protected int $obj_id;

    /**
     * Constructor
     * @param string             $a_component_id component id (e.g. "glo" for Modules/Glossary)
     * @param int                $a_obj_id       repository object id of the object that is responsible for the assignment
     * @param string             $a_item_type    item type (e.g. "term", must be unique component wide) [use "obj" if repository object wide taxonomies!]
     * @param int                $a_tax_id       taxonomy id
     * @param ilDBInterface|null $db
     * @throws ilTaxonomyException
     */
    public function __construct(
        string $a_component_id,
        int $a_obj_id,
        string $a_item_type,
        int $a_tax_id,
        ilDBInterface $db = null
    ) {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;

        if ($a_component_id == "") {
            throw new ilTaxonomyException('No component ID passed to ilTaxNodeAssignment.');
        }

        if ($a_item_type == "") {
            throw new ilTaxonomyException('No item type passed to ilTaxNodeAssignment.');
        }

        if ($a_tax_id == 0) {
            throw new ilTaxonomyException('No taxonomy ID passed to ilTaxNodeAssignment.');
        }

        $this->setComponentId($a_component_id);
        $this->setItemType($a_item_type);
        $this->setTaxonomyId($a_tax_id);
        $this->setObjectId($a_obj_id);
    }

    protected function setComponentId(string $a_val): void
    {
        $this->component_id = $a_val;
    }

    public function getComponentId(): string
    {
        return $this->component_id;
    }

    protected function setItemType(string $a_val): void
    {
        $this->item_type = $a_val;
    }

    public function getItemType(): string
    {
        return $this->item_type;
    }

    protected function setTaxonomyId(int $a_val): void
    {
        $this->taxonomy_id = $a_val;
    }

    public function getTaxonomyId(): int
    {
        return $this->taxonomy_id;
    }

    public function setObjectId(int $a_val): void
    {
        $this->obj_id = $a_val;
    }

    public function getObjectId(): int
    {
        return $this->obj_id;
    }

    /**
     * Get assignments of node
     * @param string|array $a_node_id node id
     * @return array array of tax node assignments arrays
     */
    final public function getAssignmentsOfNode($a_node_id): array
    {
        $ilDB = $this->db;

        if (is_array($a_node_id)) {
            $set = $ilDB->query(
                "SELECT * FROM tax_node_assignment " .
                " WHERE " . $ilDB->in("node_id", $a_node_id, false, "integer") .
                " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
                " AND component = " . $ilDB->quote($this->getComponentId(), "text") .
                " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
                " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
                " ORDER BY order_nr ASC"
            );
        } else {
            $set = $ilDB->query(
                "SELECT * FROM tax_node_assignment " .
                " WHERE node_id = " . $ilDB->quote($a_node_id, "integer") .
                " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
                " AND component = " . $ilDB->quote($this->getComponentId(), "text") .
                " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
                " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
                " ORDER BY order_nr ASC"
            );
        }
        $ass = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ass[] = $rec;
        }

        return $ass;
    }

    /**
     * Get assignments for item
     * @return array array of tax node assignments arrays
     */
    final public function getAssignmentsOfItem(int $a_item_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM tax_node_assignment" .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
        $ass = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ass[] = $rec;
        }
        return $ass;
    }

    /**
     * Add assignment
     * @throws ilTaxonomyException
     */
    public function addAssignment(int $a_node_id, int $a_item_id, int $a_order_nr = 0): void
    {
        $ilDB = $this->db;

        // nothing to do, if not both IDs are greater 0
        if ($a_node_id == 0 || $a_item_id == 0) {
            return;
        }

        // sanity check: does the node belong to the given taxonomy?
        $set = $ilDB->query(
            "SELECT tax_tree_id FROM tax_tree " .
            " WHERE child = " . $ilDB->quote($a_node_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if ($rec["tax_tree_id"] != $this->getTaxonomyId()) {
            throw new ilTaxonomyException('addAssignment: Node ID does not belong to current taxonomy.');
        }

        // do not re-assign, if assignment already exists
        // order number should be kept in this case
        $set2 = $ilDB->query(
            $q = "SELECT item_id FROM tax_node_assignment " .
                " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
                " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
                " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
                " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
                " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
                " AND item_id = " . $ilDB->quote($a_item_id, "integer")
        );
        if ($rec2 = $ilDB->fetchAssoc($set2)) {
            return;
        }

        if ($a_order_nr == 0) {
            $a_order_nr = $this->getMaxOrderNr($a_node_id) + 10;
        }

        $ilDB->replace(
            "tax_node_assignment",
            array(
                "node_id" => array("integer", $a_node_id),
                "component" => array("text", $this->getComponentId()),
                "item_type" => array("text", $this->getItemType()),
                "obj_id" => array("integer", $this->getObjectId()),
                "item_id" => array("integer", $a_item_id)
            ),
            array(
                "tax_id" => array("integer", $this->getTaxonomyId()),
                "order_nr" => array("integer", $a_order_nr)
            )
        );
    }

    public function deleteAssignment(int $a_node_id, int $a_item_id): void
    {
        $ilDB = $this->db;

        // nothing to do, if not both IDs are greater 0
        if ($a_node_id == 0 || $a_item_id == 0) {
            return;
        }

        // sanity check: does the node belong to the given taxonomy?
        $set = $ilDB->query(
            "SELECT tax_tree_id FROM tax_tree " .
            " WHERE child = " . $ilDB->quote($a_node_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if ((int) $rec["tax_tree_id"] != $this->getTaxonomyId()) {
            throw new ilTaxonomyException('addAssignment: Node ID does not belong to current taxonomy.');
        }

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
    }

    public function getMaxOrderNr(int $a_node_id): int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT max(order_nr) mnr FROM tax_node_assignment " .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        return (int) $rec["mnr"];
    }

    public function setOrderNr(int $a_node_id, int $a_item_id, int $a_order_nr): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "UPDATE tax_node_assignment SET " .
            " order_nr = " . $ilDB->quote($a_order_nr, "integer") .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
    }

    public function deleteAssignmentsOfItem(int $a_item_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer")
        );
    }

    public function deleteAssignmentsOfNode(int $a_node_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text")
        );
    }

    public static function deleteAllAssignmentsOfNode(int $a_node_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM tax_node_assignment WHERE " .
            " node_id = " . $ilDB->quote($a_node_id, "integer")
        );
    }

    // renumber with 10, 20, ...
    public function fixOrderNr(int $a_node_id): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM tax_node_assignment " .
            " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
            " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
            " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
            " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
            " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
            " ORDER BY order_nr ASC"
        );
        $cnt = 10;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE tax_node_assignment SET " .
                " order_nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE component = " . $ilDB->quote($this->getComponentId(), "text") .
                " AND item_type = " . $ilDB->quote($this->getItemType(), "text") .
                " AND obj_id = " . $ilDB->quote($this->getObjectId(), "integer") .
                " AND node_id = " . $ilDB->quote($a_node_id, "integer") .
                " AND tax_id = " . $ilDB->quote($this->getTaxonomyId(), "integer") .
                " AND item_id = " . $ilDB->quote($rec["item_id"], "integer")
            );
            $cnt += 10;
        }
    }

    /**
     * Find object which have assigned nodes
     */
    public static function findObjectsByNode(int $a_tax_id, array $a_node_ids, string $a_item_type): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

        $set = $ilDB->query(
            "SELECT * FROM tax_node_assignment" .
            " WHERE " . $ilDB->in("node_id", $a_node_ids, "", "integer") .
            " AND tax_id = " . $ilDB->quote($a_tax_id, "integer") .
            " AND item_type = " . $ilDB->quote($a_item_type, "text") .
            " ORDER BY order_nr ASC"
        );
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["obj_id"];
        }

        return $res;
    }
}
