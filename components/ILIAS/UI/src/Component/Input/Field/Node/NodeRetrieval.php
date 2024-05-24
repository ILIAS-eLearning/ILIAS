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

namespace ILIAS\UI\Component\Input\Field\Node;

use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Symbol\Glyph\Factory as IconFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface NodeRetrieval
{
    /**
     * This method will be called by the tree select input and multi tree select input
     * to generate the tree which is displayed on the client.
     *
     * Parts of the tree can be rendered asynchronously, by generating an @see Async node,
     * to indicate that its sub-nodes should only be loaded when they are opened/expanded.
     * In this case, an instance of this retrieval will be created by the UI framework
     * and this method is invoked again with an additional $parent_id parameter. If this
     * parameter is provided, this method is expected to generate all sub-nodes of the given
     * parent node. This process can recursively continue.
     *
     * @return \Generator<Node>
     */
    public function getNodes(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        ?string $parent_id = null,
    ): \Generator;
}
