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
 * Taxonomy node
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxonomyNode
{
    protected ilDBInterface $db;
    public string $type;
    public int $id;
    public string $title;
    protected int $order_nr = 0;
    protected int $taxonomy_id;

    /**
     * Constructor
     * @access    public
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $a_id;

        if ($a_id != 0) {
            $this->read();
        }

        $this->setType("taxn");
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setType(string $a_type): void
    {
        $this->type = $a_type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setOrderNr(int $a_val): void
    {
        $this->order_nr = $a_val;
    }

    public function getOrderNr(): int
    {
        return $this->order_nr;
    }

    public function setTaxonomyId(int $a_val): void
    {
        $this->taxonomy_id = $a_val;
    }

    public function getTaxonomyId(): int
    {
        return $this->taxonomy_id;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        if (!isset($this->data_record)) {
            $query = "SELECT * FROM tax_node WHERE obj_id = " .
                $ilDB->quote($this->id, "integer");
            $obj_set = $ilDB->query($query);
            $this->data_record = $ilDB->fetchAssoc($obj_set);
        }
        $this->setType($this->data_record["type"]);
        $this->setTitle($this->data_record["title"]);
        $this->setOrderNr((int) $this->data_record["order_nr"]);
        $this->setTaxonomyId((int) $this->data_record["tax_id"]);
    }

    public function create(): void
    {
        $ilDB = $this->db;

        if ($this->getTaxonomyId() <= 0) {
            die("ilTaxonomyNode->create: No taxonomy ID given");
        }

        // insert object data
        $id = $ilDB->nextId("tax_node");
        $query = "INSERT INTO tax_node (obj_id, title, type, create_date, order_nr, tax_id) " .
            "VALUES (" .
            $ilDB->quote($id, "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getType(), "text") . ", " .
            $ilDB->now() . ", " .
            $ilDB->quote((int) $this->getOrderNr(), "integer") . ", " .
            $ilDB->quote((int) $this->getTaxonomyId(), "integer") .
            ")";
        $ilDB->manipulate($query);
        $this->setId($id);
    }

    public function update(): void
    {
        $ilDB = $this->db;

        $query = "UPDATE tax_node SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") .
            " ,order_nr = " . $ilDB->quote((int) $this->getOrderNr(), "integer") .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");

        $ilDB->manipulate($query);
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        // delete all assignments of the node
        ilTaxNodeAssignment::deleteAllAssignmentsOfNode($this->getId());

        $query = "DELETE FROM tax_node WHERE obj_id= " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);
    }

    public function copy(int $a_tax_id = 0): ilTaxonomyNode
    {
        $taxn = new ilTaxonomyNode();
        $taxn->setTitle($this->getTitle());
        $taxn->setType($this->getType());
        $taxn->setOrderNr($this->getOrderNr());
        if ($a_tax_id == 0) {
            $taxn->setTaxonomyId($this->getTaxonomyId());
        } else {
            $taxn->setTaxonomyId($a_tax_id);
        }

        $taxn->create();

        return $taxn;
    }

    protected static function _lookup(int $a_obj_id, string $a_field): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT $a_field FROM tax_node WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        if ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            return $obj_rec[$a_field];
        }
        return "";
    }

    public static function _lookupTitle(int $a_obj_id): string
    {
        return self::_lookup($a_obj_id, "title");
    }

    public static function putInTree(
        int $a_tax_id,
        ilTaxonomyNode $a_node,
        int $a_parent_id = 0,
        int $a_target_node_id = 0,
        int $a_order_nr = 0
    ): void {
        $tax_tree = new ilTaxonomyTree($a_tax_id);

        // determine parent
        $parent_id = ($a_parent_id != 0)
            ? $a_parent_id
            : $tax_tree->readRootId();

        // determine target
        if ($a_target_node_id != 0) {
            $target = $a_target_node_id;
        } else {
            // determine last child that serves as predecessor
            $childs = $tax_tree->getChilds($parent_id);

            if (count($childs) == 0) {
                $target = ilTree::POS_FIRST_NODE;
            } else {
                $target = $childs[count($childs) - 1]["obj_id"];
            }
        }

        if ($tax_tree->isInTree($parent_id) && !$tax_tree->isInTree($a_node->getId())) {
            $tax_tree->insertNode($a_node->getId(), $parent_id, $target);
        }
    }

    // Write order nr
    public static function writeOrderNr(int $a_node_id, int $a_order_nr): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE tax_node SET " .
            " order_nr = " . $ilDB->quote($a_order_nr, "integer") .
            " WHERE obj_id = " . $ilDB->quote($a_node_id, "integer")
        );
    }

    /**
     * Write title
     */
    public static function writeTitle(int $a_node_id, string $a_title): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE tax_node SET " .
            " title = " . $ilDB->quote($a_title, "text") .
            " WHERE obj_id = " . $ilDB->quote($a_node_id, "integer")
        );
    }

    /**
     * Put this node into the taxonomy tree
     */
    public static function getNextOrderNr(int $a_tax_id, int $a_parent_id): int
    {
        $tax_tree = new ilTaxonomyTree($a_tax_id);
        if ($a_parent_id == 0) {
            $a_parent_id = $tax_tree->readRootId();
        }
        $childs = $tax_tree->getChilds($a_parent_id);
        $max = 0;

        foreach ($childs as $c) {
            if ((int) $c["order_nr"] > $max) {
                $max = (int) $c["order_nr"] + 10;
            }
        }

        return $max;
    }

    // set order nrs to 10, 20, ...
    public static function fixOrderNumbers(int $a_tax_id, int $a_parent_id): void
    {
        $tax_tree = new ilTaxonomyTree($a_tax_id);
        if ($a_parent_id == 0) {
            $a_parent_id = $tax_tree->readRootId();
        }
        $childs = $tax_tree->getChilds($a_parent_id);
        $childs = ilArrayUtil::sortArray($childs, "order_nr", "asc", true);

        $cnt = 10;
        foreach ($childs as $c) {
            self::writeOrderNr((int) $c["child"], $cnt);
            $cnt += 10;
        }
    }
}
