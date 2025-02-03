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

namespace ILIAS\UI\examples\Tree\Expandable;

/**
 * ---
 * description: >
 *   Example for rendering an expandable tree.
 *
 * expected output: >
 *   ILIAS shows an example very simliar to the "expandable" example but with less sub-points. Also the sub-points have
 *   got no functions, therefore clicking them will do nothing.
 * ---
 */
function expandable2(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $getDataNode = function (string $label, array $children = []) {
        return new class ($label, $children) {
            protected string $label = '';
            protected array $children = [];

            public function __construct(string $label, array $children = [])
            {
                $this->label = $label;
                $this->children = $children;
            }

            public function getLabel(): string
            {
                return $this->label;
            }

            public function getChildren(): array
            {
                return $this->children;
            }
        };
    };

    $n11 = $getDataNode('1.1');
    $n12 = $getDataNode('1.2', [$getDataNode('1.2.1')]);
    $n1 = $getDataNode('1', [$n11, $n12]);
    $data = [$n1];

    $recursion = new class () implements \ILIAS\UI\Component\Tree\TreeRecursion {
        public function getChildren($record, $environment = null): array
        {
            return $record->getChildren();
        }

        public function build(
            \ILIAS\UI\Component\Tree\Node\Factory $factory,
            $record,
            $environment = null
        ): \ILIAS\UI\Component\Tree\Node\Node {
            return $factory->simple($record->getLabel());
        }
    };

    $tree = $f->tree()->expandable("Label", $recursion)
        ->withData($data);

    return $renderer->render($tree);
}
