<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Tree\Node\Simple;

function simple()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Example');

    $node1 = $f->tree()->node()->simple('label');
    $node2 = $f->tree()->node()->simple('label', $icon);

    $data = [['node' => $node1], ['node' => $node2]];

    $recursion = new class () implements \ILIAS\UI\Component\Tree\TreeRecursion {
        public function getChildren($record, $environment = null): array
        {
            return [];
        }

        public function build(
            \ILIAS\UI\Component\Tree\Node\Factory $factory,
            $record,
            $environment = null
        ): \ILIAS\UI\Component\Tree\Node\Node {
            return $record['node'];
        }
    };

    $tree = $f->tree()->expandable('Label', $recursion)
              ->withData($data);

    return $renderer->render($tree);
}
