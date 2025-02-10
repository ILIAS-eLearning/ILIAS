<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\TreeSelect;

use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Input\Field\Node\Node;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;

/**
 * ---
 * description: >
 *   The example shows how to make a Tree Select Field required. This example does
 *   not contain any data processing.
 *
 * expected output: >
 *    ILIAS shows the Tree Select Field inside a Standard Form. Its label is displayed
 *    with an asterisk (*) and the Form will display according definitions above and
 *    beneath its content.
 * ---
 */
function with_required(): string
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
            yield from [];
        }
        public function getNodesAsLeaf(
            NodeFactory $node_factory,
            IconFactory $icon_factory,
            array $node_ids,
        ): \Generator {
            yield from [];
        }
    };

    $input = $factory->input()->field()->treeSelect(
        $node_retrieval,
        "select a single node",
        "this field should be marked as required.",
    );

    $input = $input->withRequired(true);

    $form = $factory->input()->container()->form()->standard('#', [$input]);

    return $renderer->render($form);
}
