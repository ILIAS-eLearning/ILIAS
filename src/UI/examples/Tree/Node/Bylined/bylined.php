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

namespace ILIAS\UI\examples\Tree\Node\Bylined;

/**
 * ---
 * description: >
 *   Example for rendering a bylined tree node.
 *
 * expected output: >
 *   ILIAS shows a collapsed tree.
 *   The first entry is titled "label" and extends to the byline "byline". Below you can see an intended "label" which
 *   functions as a link. Beneath that link a dummy text is displayed.
 *   The second entry ist also titled "label", but includes a prefixed symbol. There is also a byline and a intended label
 *   with a symbol. The label functions as link. Below the link another byline is displayed.
 *   Both entries can be collapsed/expanded. By collapsing the intended test it will disappear.
 * ---
 */
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
