<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\TreeSelect;

use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;

/**
 * Base example showing how the tree select input is used within a form.
 */
function base(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->treeSelect(
        getNodeRetrieval(),
        "select a single node",
        "you can open the select input by clicking the button above!",
    );

    return 'not implemented.';
}

/**
 * Returns a very basic implementation of the NodeRetrieval, which will not
 * work in a productive environment!
 */
function getNodeRetrieval(): NodeRetrieval
{
    return new class () implements NodeRetrieval {
        public function getNodes(
            NodeFactory $node_factory,
            GlyphFactory $icon_factory,
            ?string $parent_id = null,
        ): \Generator {
            if (null === $parent_id) {
                yield $node_factory->node(
                    '1',
                    'root',
                    null,
                    $node_factory->async('2', 'category 1'),
                    $node_factory->leaf('3', 'resource 1'),
                );
            } else {
                yield from [
                    $node_factory->async("$parent_id.1", "sub category $parent_id.1"),
                    $node_factory->leaf("$parent_id.2", "sub resource 1"),
                ];
            }
        }
    };
}
