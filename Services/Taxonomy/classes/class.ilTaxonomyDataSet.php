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
 * Taxonomy data set class
 * This class implements the following entities:
 * - tax: data from table tax_data/object_data
 * - tax_usage: data from table tax_usage
 * - tax_tree: data from a join on tax_tree and tax_node
 * - tax_node_assignment: data from table tax_node_assignment
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxonomyDataSet extends ilDataSet
{
    protected ilObjTaxonomy $current_obj;

    public function getSupportedVersions(): array
    {
        return array("4.3.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "http://www.ilias.de/xml/Services/Taxonomy/" . $a_entity;
    }

    /**
     * @inheritDoc
     */
    protected function getTypes(string $a_entity, string $a_version): array
    {
        // tax
        if ($a_entity == "tax") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "SortingMode" => "integer"
                    );
            }
        }

        // tax_usage
        if ($a_entity == "tax_usage") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "TaxId" => "integer",
                        "ObjId" => "integer"
                    );
            }
        }

        // tax_tree
        if ($a_entity == "tax_tree") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "TaxId" => "integer",
                        "Child" => "integer",
                        "Parent" => "integer",
                        "Depth" => "integer",
                        "Type" => "text",
                        "Title" => "text",
                        "OrderNr" => "integer"
                    );
            }
        }

        // tax_node_assignment
        if ($a_entity == "tax_node_assignment") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "NodeId" => "integer",
                        "Component" => "text",
                        "ItemType" => "text",
                        "ItemId" => "integer"
                    );
            }
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        // tax
        if ($a_entity == "tax") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description, " .
                        " sorting_mode" .
                        " FROM tax_data JOIN object_data ON (tax_data.id = object_data.obj_id) " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
                    break;
            }
        }

        // tax usage
        if ($a_entity == "tax_usage") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT tax_id, obj_id " .
                        " FROM tax_usage " .
                        "WHERE " .
                        $ilDB->in("tax_id", $a_ids, false, "integer"));
                    break;
            }
        }

        // tax_tree
        if ($a_entity == "tax_tree") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT tax_id, child " .
                        " ,parent,depth,type,title,order_nr " .
                        " FROM tax_tree JOIN tax_node ON (child = obj_id) " .
                        " WHERE " .
                        $ilDB->in("tax_id", $a_ids, false, "integer") .
                        " ORDER BY depth");
                    break;
            }
        }

        // tax node assignment
        if ($a_entity == "tax_node_assignment") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT node_id, component, item_type, item_id " .
                        " FROM tax_node_assignment " .
                        "WHERE " .
                        $ilDB->in("node_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        switch ($a_entity) {
            case "tax":
                return array(
                    "tax_tree" => array("ids" => $a_rec["Id"] ?? null),
                    "tax_usage" => array("ids" => $a_rec["Id"] ?? null)
                );
            case "tax_tree":
                return array(
                    "tax_node_assignment" => array("ids" => $a_rec["Child"] ?? null)
                );
        }
        return [];
    }

    ////
    //// Needs abstraction (interface?) and version handling
    ////

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        switch ($a_entity) {
            case "tax":
                $newObj = new ilObjTaxonomy();
                $newObj->create();

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setSortingMode((int) $a_rec["SortingMode"]);
                $newObj->update();

                $this->current_obj = $newObj;
                $a_mapping->addMapping("Services/Taxonomy", "tax", $a_rec["Id"], $newObj->getId());
                break;

            case "tax_tree":
                switch ($a_rec["Type"]) {
                    case "taxn":
                        $parent = (int) $a_mapping->getMapping("Services/Taxonomy", "tax_tree", $a_rec["Parent"]);
                        $tax_id = $a_mapping->getMapping("Services/Taxonomy", "tax", $a_rec["TaxId"]);
                        if ($parent == 0) {
                            $parent = $this->current_obj->getTree()->readRootId();
                        }
                        $node = new ilTaxonomyNode();
                        $node->setTitle($a_rec["Title"]);
                        $node->setOrderNr((int) $a_rec["OrderNr"]);
                        $node->setTaxonomyId((int) $tax_id);
                        $node->create();
                        ilTaxonomyNode::putInTree((int) $tax_id, $node, (int) $parent, 0, (int) $a_rec["OrderNr"]);
                        $a_mapping->addMapping(
                            "Services/Taxonomy",
                            "tax_tree",
                            $a_rec["Child"],
                            $node->getId()
                        );
                        break;

                }

            // no break
            case "tax_node_assignment":
                $new_item_id = (int) $a_mapping->getMapping(
                    "Services/Taxonomy",
                    "tax_item",
                    ($a_rec["Component"] ?? "") .
                    ":" . ($a_rec["ItemType"] ?? "") . ":" .
                    ($a_rec["ItemId"] ?? "")
                );
                $new_node_id = (int) $a_mapping->getMapping("Services/Taxonomy", "tax_tree", $a_rec["NodeId"] ?? "");

                // this is needed since 4.4 (but not exported with 4.3)
                // with 4.4 this should be part of export/import
                $new_item_id_obj = (int) $a_mapping->getMapping(
                    "Services/Taxonomy",
                    "tax_item_obj_id",
                    ($a_rec["Component"] ?? "") .
                    ":" . ($a_rec["ItemType"] ?? "") . ":" .
                    ($a_rec["ItemId"] ?? "")
                );
                if ($new_item_id > 0 && $new_node_id > 0 && $new_item_id_obj > 0) {
                    $node_ass = new ilTaxNodeAssignment(
                        $a_rec["Component"],
                        $new_item_id_obj,
                        $a_rec["ItemType"],
                        $this->current_obj->getId()
                    );
                    $node_ass->addAssignment($new_node_id, $new_item_id);
                }
                break;

            case "tax_usage":
                $usage = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", $a_rec["ObjId"]);
                if ($usage != "") {
                    $usage .= ":";
                }
                $a_mapping->addMapping(
                    "Services/Taxonomy",
                    "tax_usage_of_obj",
                    $a_rec["ObjId"],
                    $usage . $this->current_obj->getId()
                );
                break;
        }
    }
}
