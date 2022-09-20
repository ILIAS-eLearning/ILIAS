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
 * Taxonomy
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjTaxonomy extends ilObject2
{
    public const SORT_ALPHABETICAL = 0;
    public const SORT_MANUAL = 1;

    protected array $node_mapping = array();
    protected bool $item_sorting = false;
    protected int $sorting_mode = self::SORT_ALPHABETICAL;

    /**
     * ilObjTaxonomy constructor.
     * @param int $a_id
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "tax";
        parent::__construct($a_id, false);
    }

    protected function initType(): void
    {
        $this->type = "tax";
    }

    public function setSortingMode(int $a_val): void
    {
        $this->sorting_mode = $a_val;
    }

    public function getSortingMode(): int
    {
        return $this->sorting_mode;
    }

    public function setItemSorting(bool $a_val): void
    {
        $this->item_sorting = $a_val;
    }

    public function getItemSorting(): bool
    {
        return $this->item_sorting;
    }

    public function getTree(): ?ilTaxonomyTree
    {
        if ($this->getId() > 0) {
            return new ilTaxonomyTree($this->getId());
        }
        return null;
    }

    // node mapping is used during cloning
    public function getNodeMapping(): array
    {
        return $this->node_mapping;
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $ilDB = $this->db;

        // create tax data record
        $ilDB->manipulate("INSERT INTO tax_data " .
            "(id, sorting_mode, item_sorting) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getSortingMode(), "integer") . "," .
            $ilDB->quote((int) $this->getItemSorting(), "integer") .
            ")");
        $node = new ilTaxonomyNode();
        $node->setType("");    // empty type
        $node->setTitle("Root node for taxonomy " . $this->getId());
        $node->setTaxonomyId($this->getId());
        $node->create();
        $tax_tree = new ilTaxonomyTree($this->getId());
        $tax_tree->addTree($this->getId(), $node->getId());
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        assert($new_obj instanceof ilObjTaxonomy);
        $new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setSortingMode($this->getSortingMode());

        $this->node_mapping = array();

        $this->cloneNodes(
            $new_obj,
            $new_obj->getTree()->readRootId(),
            $this->getTree()->readRootId()
        );
    }

    // clone nodes of taxonomy
    public function cloneNodes(
        ilObjTaxonomy $a_new_obj,
        string $a_target_parent,
        string $a_source_parent
    ): void {
        // get all childs
        $nodes = $this->getTree()->getChilds($a_source_parent);
        foreach ($nodes as $node) {
            if ($node["type"] === "taxn") {
                $tax_node = new ilTaxonomyNode($node["child"]);
                $new_node = $tax_node->copy($a_new_obj->getId());

                ilTaxonomyNode::putInTree(
                    $a_new_obj->getId(),
                    $new_node,
                    $a_target_parent
                );

                $this->node_mapping[$node["child"]] = $new_node->getId();

                // handle childs
                $this->cloneNodes($a_new_obj, $new_node->getId(), $node["child"]);
            }
        }
    }

    protected function doDelete(): void
    {
        $ilDB = $this->db;

        // delete usages
        self::deleteUsagesOfTaxonomy($this->getId());

        // get all nodes
        $tree = $this->getTree();
        $subtree = $tree->getSubTreeIds($tree->readRootId());
        $subtree[] = $tree->readRootId();

        // get root node data (important: must happen before we
        // delete the nodes
        $root_node_data = $tree->getNodeData($tree->readRootId());
        foreach ($subtree as $node_id) {
            // delete node (this also deletes its assignments)
            $node = new ilTaxonomyNode($node_id);
            $node->delete();
        }

        // delete the tree
        $tree->deleteTree($root_node_data);

        // delete taxonoymy properties record
        $ilDB->manipulate(
            "DELETE FROM tax_data WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    protected function doRead(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM tax_data " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->setSortingMode((int) $rec["sorting_mode"]);
        $this->setItemSorting((bool) $rec["item_sorting"]);
    }

    protected function doUpdate(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            $t = "UPDATE tax_data SET " .
                " sorting_mode = " . $ilDB->quote($this->getSortingMode(), "integer") . ", " .
                " item_sorting = " . $ilDB->quote((int) $this->getItemSorting(), "integer") .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    public static function loadLanguageModule(): void
    {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule("tax");
    }

    public static function saveUsage(int $a_tax_id, int $a_obj_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_tax_id > 0 && $a_obj_id > 0) {
            $ilDB->replace(
                "tax_usage",
                array("tax_id" => array("integer", $a_tax_id),
                      "obj_id" => array("integer", $a_obj_id)
                ),
                array()
            );
        }
    }

    /**
     * @param int  $a_obj_id         object id
     * @param bool $a_include_titles include titles in array
     * @return array array of tax IDs or array of arrays with keys tax_id, title
     */
    public static function getUsageOfObject(int $a_obj_id, bool $a_include_titles = false): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT tax_id FROM tax_usage " .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
        $tax = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!$a_include_titles) {
                $tax[] = (int) $rec["tax_id"];
            } else {
                $tax[] = array("tax_id" => (int) $rec["tax_id"],
                               "title" => ilObject::_lookupTitle((int) $rec["tax_id"])
                );
            }
        }
        return $tax;
    }

    // Delete all usages of a taxonomy
    public static function deleteUsagesOfTaxonomy(int $a_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM tax_usage WHERE " .
            " tax_id = " . $ilDB->quote($a_id, "integer")
        );
    }

    /**
     * Get all assigned items under a node
     * @param string $a_comp
     * @param int    $a_obj_id
     * @param string $a_item_type
     * @param int    $a_tax_id
     * @param        $a_node
     * @return array
     * @throws ilTaxonomyException
     */
    public static function getSubTreeItems(
        string $a_comp,
        int $a_obj_id,
        string $a_item_type,
        int $a_tax_id,
        $a_node
    ): array {
        $tree = new ilTaxonomyTree($a_tax_id);

        $sub_nodes = $tree->getSubTreeIds($a_node);
        $sub_nodes[] = $a_node;

        $tn_ass = new ilTaxNodeAssignment($a_comp, $a_obj_id, $a_item_type, $a_tax_id);

        return $tn_ass->getAssignmentsOfNode($sub_nodes);
    }

    // lookup property in tax_data record
    protected static function lookup(string $a_field, int $a_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT " . $a_field . " FROM tax_data " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        return $rec[$a_field];
    }

    public static function lookupSortingMode(int $a_id): int
    {
        return (int) self::lookup("sorting_mode", $a_id);
    }
}
