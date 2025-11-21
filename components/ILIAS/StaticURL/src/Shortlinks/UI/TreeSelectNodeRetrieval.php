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
 */

declare(strict_types=1);

namespace ILIAS\StaticURL\Shortlinks\UI;

use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Component\Symbol\Icon;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
readonly class TreeSelectNodeRetrieval implements Field\Node\NodeRetrieval
{
    /** node attribute used to sort entries, @see ilTree::getChildsByTypeFilter() */
    protected const string NODE_SORT_BY = 'title';

    /** @var string[] (combined result of branch and leaf object type list) */
    protected array $node_object_type_list;

    /**
     * @param string[] $branch_object_type_list (object types which contain children)
     * @param string[] $leaf_object_type_list   (object types which have no children)
     */
    public function __construct(
        protected \ILIAS\Language\Language $language,
        protected \ILIAS\Data\Factory $data_factory,
        protected \ILIAS\UI\URLBuilderToken $async_node_id_parameter,
        protected \ILIAS\UI\URLBuilder $async_node_url_builder,
        protected \ilAccess $access,
        protected \ilTree $tree,
        protected array $branch_object_type_list,
        protected array $leaf_object_type_list,
        protected int $max_branch_node_depth,
        protected int $root_node_id,
    ) {
        $this->node_object_type_list = array_merge($this->branch_object_type_list, $this->leaf_object_type_list);
    }

    public function getNodes(
        Field\Node\Factory $node_factory,
        Icon\Factory $icon_factory,
        array $sync_node_id_whitelist = [],
        ?string $parent_id = null,
    ): \Generator {
        yield from $this->createNodes(
            $node_factory,
            $icon_factory,
            $sync_node_id_whitelist,
            (int) ($parent_id ?? $this->root_node_id),
        );
    }

    public function getNodesAsLeaf(
        Field\Node\Factory $node_factory,
        Icon\Factory $icon_factory,
        array $node_ids
    ): \Generator {
        foreach ($node_ids as $node_id) {
            try {
                $tree_node_data = $this->tree->getNodeData((int) $node_id);
                [$object_ref_id, $object_type, $object_title] = $this->extractObjectDataOrAbort($tree_node_data);
                yield $node_factory->leaf(
                    $object_ref_id,
                    $object_title,
                    $this->getObjectIcon($icon_factory, $object_ref_id, $object_type),
                );
            } catch (\LogicException) {
                yield $node_factory->leaf($node_id, $this->language->txt('unknown'));
            }
        }
    }

    /** @return Field\Node\Node[] */
    protected function createNodes(
        Field\Node\Factory $node_factory,
        Icon\Factory $icon_factory,
        array $max_depth_node_id_whitelist,
        int $parent_object_id,
        int $depth = 0,
    ): array {
        $children = $this->tree->getChildsByTypeFilter(
            $parent_object_id,
            $this->node_object_type_list,
            self::NODE_SORT_BY,
        );

        $nodes = [];
        foreach ($children as $child) {
            [$object_ref_id, $object_type, $object_title] = $this->extractObjectDataOrAbort($child);

            $is_object_visible = $this->isObjectVisible($object_ref_id, $object_type);
            $is_object_container = $this->isObjectContainer($object_type);

            // append visible children of an invisible branch node to the current node.
            if (!$is_object_visible && $is_object_container) {
                $nodes = [...$nodes, ...$this->createNodes(
                    $node_factory,
                    $icon_factory,
                    $max_depth_node_id_whitelist,
                    $object_ref_id,
                    $depth + 1
                )];
                continue;
            }

            $object_icon = $this->getObjectIcon($icon_factory, $object_ref_id, $object_type);

            if ($is_object_visible && $is_object_container) {
                if ($depth < $this->max_branch_node_depth || in_array($object_ref_id, $max_depth_node_id_whitelist, true)) {
                    $nodes[] = $node_factory->branch(
                        $object_ref_id,
                        $object_title,
                        $object_icon,
                        ...$this->createNodes(
                            $node_factory,
                            $icon_factory,
                            $max_depth_node_id_whitelist,
                            $object_ref_id,
                            $depth + 1
                        ),
                    );
                } else {
                    $nodes[] = $node_factory->async(
                        $this->getAsyncRenderUrl($object_ref_id),
                        $object_ref_id,
                        $object_title,
                        $object_icon,
                    );
                }
                continue;
            }
            if ($is_object_visible && !$is_object_container) {
                $nodes[] = $node_factory->leaf($object_ref_id, $object_title, $object_icon);
            }
        }
        return $nodes;
    }

    /** @return array{0: int, 1: string, 2: string} (object ref-id, type, title) */
    protected function extractObjectDataOrAbort(array $tree_node_data): array
    {
        $object_ref_id = $tree_node_data['ref_id'] ?? $tree_node_data['child'] ?? null;
        $object_type = $tree_node_data['type'] ?? null;
        $object_title = $tree_node_data['title'] ?? null;
        if (null === $object_ref_id || null === $object_type || null === $object_title) {
            throw new \LogicException("Object data is invalid");
        }
        return [$object_ref_id, $object_type, $object_title];
    }

    protected function getAsyncRenderUrl(int $object_ref_id): \ILIAS\Data\URI
    {
        $async_node_url_builder = $this->async_node_url_builder->withParameter(
            $this->async_node_id_parameter,
            (string) $object_ref_id
        );
        return $this->data_factory->uri((string) $async_node_url_builder->buildURI());
    }

    protected function getObjectIcon(Icon\Factory $icon_factory, int $object_ref_id, string $object_type): Icon\Icon
    {
        // ensures plugin object types are properly handled as well.
        $icon_path = \ilObject::_getIcon(\ilObject::_lookupObjectId($object_ref_id), Icon\Icon::SMALL);

        if ($this->tree->getRootId() >= $object_ref_id) {
            $alt_text = $this->language->txt('repository');
        } else {
            $alt_text = $this->language->txt($object_type);
        }
        return $icon_factory->custom($icon_path, $alt_text, Icon\Icon::SMALL);
    }

    protected function isObjectVisible(int $object_ref_id, string $object_type = ''): bool
    {
        return $this->access->checkAccess('visible', '', $object_ref_id, $object_type);
    }

    protected function isObjectContainer(string $object_type): bool
    {
        return in_array($object_type, $this->branch_object_type_list, true);
    }
}
