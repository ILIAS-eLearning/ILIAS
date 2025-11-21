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
 */

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\TreeSelect;

use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Input\Field\Node\Node;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;

/**
 * ---
 * description: >
 *   The example shows how to disable a Tree Select Field. This example does not
 *   contain any data processing.
 *
 * expected output: >
 *   ILIAS shows the Tree Select Field inside a Standard Form. The "Select" Shy Button
 *   is disabled and cannot be operated. Clicking the label or Shy Button will not open
 *   the Modal.
 * ---
 */
function with_disabled(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $node_retrieval = new class () implements NodeRetrieval {
        public function getNodes(
            NodeFactory $node_factory,
            IconFactory $icon_factory,
            array $sync_node_id_whitelist = [],
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
        "you should not be able to click the button above!",
    );

    $input = $input->withDisabled(true);

    $form = $factory->input()->container()->form()->standard('#', [$input]);

    return $renderer->render($form);
}
