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

namespace ILIAS\UI\examples\Tree\Node\Simple;

/**
 * ---
 * description: >
 *   Example for rendering a simple tree node.
 *
 * expected output: >
 *   ILIAS shows a list with two entries. The first entry contents of the single word "label". The second entry shows an
 *   icon and a word.
 * ---
 */
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
