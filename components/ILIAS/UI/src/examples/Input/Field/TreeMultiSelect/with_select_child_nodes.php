<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\TreeMultiSelect;

use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Input\Field\Node\Node;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;

/**
 * ---
 * description: >
 *   The example shows how to change the default behaviour of selecting/choosing Nodes with a
 *   Tree Multi Select Field. This example does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows the Tree Multi Select Field inside a Standard Form. The look and behaviour is
 *   the same as in the base example, except, when selecting/choosing multiple Nodes inside the
 *   Drilldown Menu, its now possible to include nested child-Nodes. Clicking a Bulky Button to
 *   select a Node will not disable Bulky Buttons of other Nodes.
 * ---
 */
function with_select_child_nodes(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $node_retrieval = new class () implements NodeRetrieval {
        public function getNodes(
            NodeFactory $node_factory,
            IconFactory $icon_factory,
            ?string $parent_id = null,
        ): \Generator {
            yield $node_factory->branch('1', 'branch node 1', null,
                $node_factory->branch('1.1', 'branch node 1.1', null,
                    $node_factory->leaf('1.1.1', 'leaf node 1.1.1'),
                    $node_factory->leaf('1.1.2', 'leaf node 1.1.2'),
                ),
                $node_factory->leaf('1.2', 'leaf node 1.2'),
            );
        }
        public function getNodesAsLeaf(
            NodeFactory $node_factory,
            IconFactory $icon_factory,
            array $node_ids,
        ): \Generator {
            yield from [];
        }
    };

    $input = $factory->input()->field()->treeMultiSelect(
        $node_retrieval,
        "select multiple nodes",
        "you can also select child-nodes of selected nodes!",
    );

    $input = $input->withSelectChildNodes(true);

    $form = $factory->input()->container()->form()->standard('#', [$input]);

    return $renderer->render($form);
}
