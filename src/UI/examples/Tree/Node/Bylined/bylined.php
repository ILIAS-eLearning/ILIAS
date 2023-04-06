<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Tree\Node\Bylined;

function bylined()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Example');
    $long_byline = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
        sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquy';

    $node1 = $f->tree()->node()->bylined('label', 'byline');
    $node2 = $f->tree()->node()->bylined('label', $long_byline)
               ->withLink(new \ILIAS\Data\URI('https://docu.ilias.de'));
    $node3 = $f->tree()->node()->bylined('label', 'byline', $icon);
    $node4 = $f->tree()->node()->bylined('label', 'byline', $icon)
               ->withLink(new \ILIAS\Data\URI('https://docu.ilias.de'));
    $data = [['node' => $node1, 'children' => [
        ['node' => $node2]]],
             ['node' => $node3, 'children' => [
                 ['node' => $node4]],
             ]
    ];

    $recursion = new class () implements \ILIAS\UI\Component\Tree\TreeRecursion {
        public function getChildren($record, $environment = null): array
        {
            return $record['children'] ?? [];
        }

        public function build(
            \ILIAS\UI\Component\Tree\Node\Factory $factory,
            $record,
            $environment = null
        ): \ILIAS\UI\Component\Tree\Node\Node {
            $node = $record['node'];
            if (isset($record['children'])) {
                $node = $node->withExpanded(true);
            }
            return $node;
        }
    };

    $tree = $f->tree()->expandable('Label', $recursion)
              ->withData($data);

    return $renderer->render($tree);
}
