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
 * Taxonomy classification provider
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilTaxonomyClassificationProvider extends ilClassificationProvider
{
    protected array $selection = [];
    protected static array $valid_tax_map = [];
    protected int $incoming_id = 0;

    public static function isActive(int $a_parent_ref_id, int $a_parent_obj_id, string $a_parent_obj_type): bool
    {
        return (bool) self::getActiveTaxonomiesForParentRefId($a_parent_ref_id);
    }

    protected function init(): void
    {
        $params = $this->request->getQueryParams();
        $body = $this->request->getParsedBody();
        $this->incoming_id = (int) ($body["tax_node"] ?? ($params["tax_node"] ?? null));
    }

    public function render(array &$a_html, object $a_parent_gui): void
    {
        foreach (self::$valid_tax_map[$this->parent_ref_id] as $tax_id) {
            $tax_exp = new ilTaxonomyExplorerGUI($a_parent_gui, "", (int) $tax_id, "", "");
            $tax_exp->setSkipRootNode(true);
            $tax_exp->setOnClick("il.Classification.toggle({tax_node: '{NODE_CHILD}'});");

            if (isset($this->selection) && is_array($this->selection)) {
                foreach ($this->selection as $node_id) {
                    $tax_exp->setPathOpen($node_id);
                    $tax_exp->setNodeSelected($node_id);
                }
            }

            if (!$tax_exp->handleCommand()) {
                $a_html[] = array(
                    "title" => ilObject::_lookupTitle((int) $tax_id),
                    "html" => $tax_exp->getHTML()
                );
            }
        }
    }

    public function importPostData(?array $a_saved = null): array
    {
        $incoming_id = $this->incoming_id;
        if ($incoming_id !== 0) {
            if (is_array($a_saved)) {
                foreach ($a_saved as $idx => $node_id) {
                    if ($node_id == $incoming_id) {
                        unset($a_saved[$idx]);
                        return $a_saved;
                    }
                }
                $a_saved[] = $incoming_id;
                return $a_saved;
            } else {
                return array($incoming_id);
            }
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setSelection(array $a_value): void
    {
        $this->selection = $a_value;
    }

    protected static function getActiveTaxonomiesForParentRefId(int $a_parent_ref_id): int
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        if (!isset(self::$valid_tax_map[$a_parent_ref_id])) {
            $prefix = ilObjCategoryGUI::CONTAINER_SETTING_TAXBLOCK;

            $all_valid = array();
            foreach ($tree->getPathFull($a_parent_ref_id) as $node) {
                if ($node["type"] == "cat") {
                    $node_valid = array();

                    if (ilContainer::_lookupContainerSetting(
                        (int) $node["obj_id"],
                        ilObjectServiceSettingsGUI::TAXONOMIES,
                        false
                    ) !== '') {
                        $all_valid = array_merge(
                            $all_valid,
                            ilObjTaxonomy::getUsageOfObject((int) $node["obj_id"])
                        );

                        $active = array();
                        foreach (ilContainer::_getContainerSettings((int) $node["obj_id"]) as $keyword => $value) {
                            if (substr($keyword, 0, strlen($prefix)) == $prefix && (bool) $value) {
                                $active[] = substr($keyword, strlen($prefix));
                            }
                        }

                        $node_valid = array_intersect($all_valid, $active);
                    }

                    if (count($node_valid) !== 0) {
                        foreach ($node_valid as $idx => $node_id) {
                            // #15268 - deleted taxonomy?
                            if (ilObject::_lookupType((int) $node_id) != "tax") {
                                unset($node_valid[$idx]);
                            }
                        }
                    }

                    self::$valid_tax_map[$node["ref_id"]] = $node_valid;
                }
            }
        }

        if (isset(self::$valid_tax_map[$a_parent_ref_id]) && is_array(self::$valid_tax_map[$a_parent_ref_id])) {
            return count(self::$valid_tax_map[$a_parent_ref_id]);
        }

        return 0;
    }

    public function getFilteredObjects(): array
    {
        $tax_obj_ids = array();
        $tax_map = array();

        // :TODO: this could be smarter
        foreach ($this->selection as $node_id) {
            $node = new ilTaxonomyNode((int) $node_id);
            $tax_map[$node->getTaxonomyId()][] = (int) $node_id;
        }

        foreach ($tax_map as $tax_id => $node_ids) {
            $tax_tree = new ilTaxonomyTree((int) $tax_id);

            // combine taxonomy nodes OR
            $tax_nodes = array();
            foreach ($node_ids as $node_id) {
                $tax_nodes = array_merge($tax_nodes, $tax_tree->getSubTreeIds((int) $node_id));
                $tax_nodes[] = (int) $node_id;
            }

            $tax_obj_ids[$tax_id] = ilTaxNodeAssignment::findObjectsByNode((int) $tax_id, $tax_nodes, "obj");
        }

        // combine taxonomies AND
        $obj_ids = null;
        foreach ($tax_obj_ids as $tax_objs) {
            $obj_ids = $obj_ids === null ? $tax_objs : array_intersect($obj_ids, $tax_objs);
        }

        return (array) $obj_ids;
    }
}
