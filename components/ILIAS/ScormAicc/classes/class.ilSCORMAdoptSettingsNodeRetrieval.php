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

use ILIAS\Data\URI;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class ilSCORMAdoptSettingsNodeRetrieval implements NodeRetrieval
{
    protected const string NODE_SORT_BY = 'title';

    protected int $current_obj_id;
    protected int $max_branch_node_depth;

    /** @var string[] */
    protected array $node_object_types;


    /**
     * @param string[] $branch_object_types
     */
    public function __construct(
        protected int $current_ref_id,
        protected string $target_subtype,
        protected \Closure $async_uri_provider,
        protected ilLanguage $language,
        protected ilAccess $access,
        protected ilTree $tree,
        protected array $branch_object_types,
        int $max_branch_node_depth = 2,
    ) {
        $this->current_obj_id = ilObject::_lookupObjectId($current_ref_id);
        $this->max_branch_node_depth = max(0, $max_branch_node_depth);
        $this->node_object_types = array_values(
            array_unique(
                [...$this->branch_object_types, 'sahs'],
            ),
        );
    }

    public function getNodes(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        array $sync_node_id_whitelist = [],
        ?string $parent_id = null,
    ): \Generator {
        yield from $this->createNodes(
            $node_factory,
            $icon_factory,
            $sync_node_id_whitelist,
            (int) ($parent_id ?? $this->tree->getParentId($this->tree->getRootId())),
        );
    }

    public function getNodesAsLeaf(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        array $node_ids,
    ): \Generator {
        foreach ($node_ids as $node_id) {
            $ref_id = (int) $node_id;
            if (!$this->isSelectableSource($ref_id)) {
                yield $node_factory->leaf([(string) $node_id], $this->language->txt('unknown'));
                continue;
            }

            $node_data = $this->tree->getNodeData($ref_id);
            $path = $this->tree->getPathId($ref_id);
            $title = (string) ($node_data['title'] ?? '');
            if ($path === [] || $title === '') {
                yield $node_factory->leaf([(string) $node_id], $this->language->txt('unknown'));
                continue;
            }

            yield $node_factory->leaf(
                $path,
                $title,
                $this->getObjectIcon($icon_factory, $ref_id, 'sahs'),
            );
        }
    }

    /**
     * @return \ILIAS\UI\Component\Input\Field\Node\Node[]
     */
    protected function createNodes(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        array $sync_node_id_whitelist,
        int $parent_ref_id,
        int $depth = 0,
    ): array {
        $nodes = [];
        $children = $this->tree->getChildsByTypeFilter(
            $parent_ref_id,
            $this->node_object_types,
            self::NODE_SORT_BY,
        );

        foreach ($children as $child) {
            $ref_id = (int) ($child['ref_id'] ?? $child['child'] ?? 0);
            $type = (string) ($child['type'] ?? '');
            $title = (string) ($child['title'] ?? '');
            $path = $this->tree->getPathId($ref_id);

            if ($ref_id <= 0 || $path === [] || $title === '') {
                continue;
            }

            $is_visible = $this->isObjectVisible($ref_id, $type);
            $is_container = $this->isObjectContainer($type);

            // Append visible children of an invisible branch node to the current level.
            if (!$is_visible && $is_container) {
                $nodes = [
                    ...$nodes,
                    ...$this->createNodes(
                        $node_factory,
                        $icon_factory,
                        $sync_node_id_whitelist,
                        $ref_id,
                        $depth + 1,
                    ),
                ];
                continue;
            }

            if ($is_visible && $is_container) {
                $icon = $this->getObjectIcon($icon_factory, $ref_id, $type);
                if ($depth < $this->max_branch_node_depth || in_array($ref_id, $sync_node_id_whitelist, true)) {
                    $child_nodes = $this->createNodes(
                        $node_factory,
                        $icon_factory,
                        $sync_node_id_whitelist,
                        $ref_id,
                        $depth + 1,
                    );
                    if ($child_nodes !== []) {
                        $nodes[] = $node_factory->branch(
                            $path,
                            $title,
                            $icon,
                            ...$child_nodes,
                        );
                    }
                } elseif ($this->hasDisplayableChildren($ref_id)) {
                    $nodes[] = $node_factory->async(
                        $this->getAsyncRenderUri($ref_id),
                        $path,
                        $title,
                        $icon,
                    );
                }
                continue;
            }

            if ($is_visible && $type === 'sahs' && $this->isSelectableSource($ref_id)) {
                $nodes[] = $node_factory->leaf(
                    $path,
                    $title,
                    $this->getObjectIcon($icon_factory, $ref_id, $type),
                );
            }
        }

        return $nodes;
    }

    protected function getObjectIcon(IconFactory $icon_factory, int $object_ref_id, string $object_type): Icon
    {
        $icon_path = ilObject::_getIcon(ilObject::_lookupObjectId($object_ref_id), Icon::SMALL);
        $alt_text = $this->tree->getRootId() >= $object_ref_id
            ? $this->language->txt('repository')
            : $this->language->txt($object_type);

        return $icon_factory->custom($icon_path, $alt_text, Icon::SMALL);
    }

    protected function getAsyncRenderUri(int $parent_ref_id): URI
    {
        $provider = $this->async_uri_provider;
        return $provider($parent_ref_id);
    }

    protected function isSelectableSource(int $ref_id): bool
    {
        if ($ref_id <= 0 || $ref_id === $this->current_ref_id) {
            return false;
        }

        $object_id = ilObject::_lookupObjectId($ref_id);
        if (
            $object_id <= 0
            || $object_id === $this->current_obj_id
            || ilObject::_lookupType($object_id) !== 'sahs'
        ) {
            return false;
        }

        if (!$this->access->checkAccess('write', '', $ref_id, 'sahs')) {
            return false;
        }

        return ilObjSAHSLearningModule::_lookupSubType($object_id) === $this->target_subtype;
    }

    protected function hasDisplayableChildren(int $parent_ref_id): bool
    {
        return $this->tree->getChildsByTypeFilter(
            $parent_ref_id,
            $this->node_object_types,
            self::NODE_SORT_BY,
        ) !== [];
    }

    protected function isObjectVisible(int $object_ref_id, string $object_type = ''): bool
    {
        return $this->access->checkAccess('visible', '', $object_ref_id, $object_type);
    }

    protected function isObjectContainer(string $object_type): bool
    {
        return in_array($object_type, $this->branch_object_types, true);
    }
}
