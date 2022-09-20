<?php declare(strict_types=1);

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

namespace ILIAS\UI\examples\Tree\Node\KeyValue;

function key_value()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Example');

    $node1 = $f->tree()->node()->keyValue('label', 'value');
    $node2 = $f->tree()->node()->keyValue('label', 'value')
                               ->withLink(new \ILIAS\Data\URI('https://docu.ilias.de'));
    $node3 = $f->tree()->node()->keyValue('label', 'value', $icon);
    $node4 = $f->tree()->node()->keyValue('label', 'value', $icon)
                               ->withLink(new \ILIAS\Data\URI('https://docu.ilias.de'));
    $data = [['node' => $node1, 'children' => [
            ['node' => $node2]]],
         ['node' => $node3, 'children' => [
             ['node' => $node4]],
         ]
    ];

    $recursion = new class implements \ILIAS\UI\Component\Tree\TreeRecursion {
        public function getChildren($record, $environment = null) : array
        {
            return $record['children'] ?? [];
        }

        public function build(
            \ILIAS\UI\Component\Tree\Node\Factory $factory,
            $record,
            $environment = null
        ) : \ILIAS\UI\Component\Tree\Node\Node {
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
