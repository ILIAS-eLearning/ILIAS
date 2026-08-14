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

use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Input\Field\Node\Node;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;

readonly class ilDataCollectionNodeRetrieval implements NodeRetrieval
{
    private ilTree $tree;
    private ilRbacSystem $rbac;
    private ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->tree = $DIC->repositoryTree();
        $this->rbac = $DIC->rbac()->system();
        $this->lng = $DIC->language();

    }
    public function getNodes(NodeFactory $node_factory, IconFactory $icon_factory, array $sync_node_id_whitelist = [], ?string $parent_id = null): Generator
    {
        $parent_id = (int) $parent_id ?: $this->tree->getRootId();
        if (($parent_id === ROOT_FOLDER_ID || $this->tree->getNodeData($parent_id)['owner'] > 0) && $this->rbac->checkAccess('read', $parent_id)) {
            $obj_id = ilObject::_lookupObjId($parent_id);
            $children = [];
            foreach ($this->tree->getChildIds($parent_id) as $node) {
                $child = $this->getNodes($node_factory, $icon_factory, [], (string) $node)->current();
                if ($child) {
                    $children[] = $child;
                }
            }
            if ($children === []) {
                yield $node_factory->leaf(
                    [$parent_id],
                    ilObject::_lookupTitle($obj_id),
                    $icon_factory->standard(ilObject::_lookupType($obj_id), '')
                );
            } else {
                yield $node_factory->branch(
                    [$parent_id],
                    ilObject::_lookupTitle($obj_id),
                    $icon_factory->standard(ilObject::_lookupType($obj_id), ''),
                    ...$children
                );
            }
        }
    }

    public function getNodesAsLeaf(NodeFactory $node_factory, IconFactory $icon_factory, array $node_ids): Generator
    {
        foreach ($node_ids as $node_id) {
            $node_id = (int) $node_id;
            if (($node_id === ROOT_FOLDER_ID || $this->tree->getNodeData($node_id)['owner'] > 0) && $this->rbac->checkAccess('read', $node_id)) {
                $obj_id = ilObject::_lookupObjId($node_id);
                yield $node_factory->leaf(
                    [$node_id],
                    ilObject::_lookupTitle($obj_id),
                    $icon_factory->standard(ilObject::_lookupType($obj_id), '')
                );
            } else {
                yield $node_factory->leaf(
                    [$node_id],
                    $this->lng->txt('obj_not_found'),
                );
            }
        }
    }
}
