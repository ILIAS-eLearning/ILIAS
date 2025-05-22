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

class ilTaxonomyClassificationExplorerGUI extends ilTaxonomyExplorerGUI
{
    public function __construct(
        $parent_obj,
        string $parent_cmd,
        int $tax_id,
        string $target_gui,
        string $target_cmd,
        string $id = ""
    ) {
        global $DIC;

        parent::__construct(
            $parent_obj,
            $parent_cmd,
            $tax_id,
            $target_gui,
            $target_cmd,
            $id
        );
    }

    public function getNodeContent($a_node): string
    {
        $rn = $this->getRootNode();
        if ($rn["child"] == $a_node["child"]) {
            $title = ilObject::_lookupTitle($this->tax_tree->getTreeId());
        } else {
            $title = $a_node["title"];
        }
        return $title;
    }

    protected function createNode(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $record
    ): \ILIAS\UI\Component\Tree\Node\Node {
        $nodeIconPath = $this->getNodeIcon($record);

        $icon = null;
        if ($nodeIconPath !== '') {
            $icon = $this->ui
                ->factory()
                ->symbol()
                ->icon()
                ->custom($nodeIconPath, $this->getNodeIconAlt($record));
        }

        $node = $factory->simple($this->getNodeContent($record), $icon)
                       ->withLink(new \ILIAS\Data\URI(ILIAS_HTTP_PATH . "/#tax_node_" . $record["child"]));
        if (in_array($this->getNodeId($record), $this->selected_nodes)) {
            $node = $node->withHighlighted(true);
        }
        return $node;
    }


}
