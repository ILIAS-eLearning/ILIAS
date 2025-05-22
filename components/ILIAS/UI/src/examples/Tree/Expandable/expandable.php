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
 *   ILIAS shows a navigation tree with different numbered sub-points. The sub-point can be collapsed by clicking the ">".
 *   The last entries in a branch are clickable and open a modal window with an image.
 * ---
 */
function expandable()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $data = [
        ['label' => 'root', 'children' => [
            ['label' => '1', 'children' => [
                ['label' => '1.1', 'children' => [
                    ['label' => '1.1.1', 'children' => []],
                    ['label' => '1.1.2', 'children' => []]
                ]],
                ['label' => '1.2', 'children' => []],
                ['label' => '1.3', 'children' => []]
            ]],
            ['label' => '2', 'children' => [
                ['label' => '2.1', 'children' => []],
            ]],
            ['label' => '3', 'children' => [
                ['label' => '3.1', 'children' => [
                    ['label' => '3.1.1', 'children' => [
                        ['label' => '3.1.1.1', 'children' => []],
                    ]],
                ]],

            ]],
        ]]
    ];

    $recursion = new class () implements \ILIAS\UI\Component\Tree\TreeRecursion {
        public function getChildren($record, $environment = null): array
        {
            return $record['children'];
        }

        public function build(
            \ILIAS\UI\Component\Tree\Node\Factory $factory,
            $record,
            $environment = null
        ): \ILIAS\UI\Component\Tree\Node\Node {
            $label = $record['label'];
            $node = $factory->simple($label);

            if (count($record['children']) === 0) {
                $node = $node->withOnClick($environment['modal']->getShowSignal());
            }

            if ($label === "root" || $label === "2") {
                $node = $node->withExpanded(true);
            }
            if ($label === "2") {
                $node = $node->withHighlighted(true);
            }

            return $node;
        }
    };

    $image = $f->image()->responsive("assets/ui-examples/images/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
    $page = $f->modal()->lightboxImagePage($image, 'Mountains');
    $modal = $f->modal()->lightbox($page);
    $environment = [
        'modal' => $modal
    ];

    $tree = $f->tree()->expandable("Label", $recursion)
        ->withEnvironment($environment)
        ->withData($data)
        ->withHighlightOnNodeClick(true);

    return $renderer->render([
        $modal,
        $tree
    ]);
}
