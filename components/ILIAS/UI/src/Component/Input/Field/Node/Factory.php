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

use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     *  ---
     *  description:
     *    purpose: >
     *      A node is used by the tree select input and multi tree select input to visualise an entry
     *      inside the tree which can own sub-nodes. Nodes consist of a unique identifier, name and
     *      optional icon. It can represent any kind of data.
     *    rivals:
     *      Async Node: >
     *          An async node should be used if the sub-nodes of a node should be rendered asynchronously
     *          on the client.
     *      Leaf Node: A leaf node should be used if the node cannot own any sub-nodes.
     *    rules:
     *       usage:
     *           1: >
     *              Icons SHOULD reflect the kind of data a node represents as closely as possible.
     *              If there is only one kind of data, an icon SHOULD NOT be provided.
     *  ---
     * @param string                                      $id
     * @param string                                      $name
     * @param \ILIAS\UI\Component\Input\Field\Node\Node   $children
     * @param \ILIAS\UI\Component\Symbol\Glyph\Glyph|null $icon
     * @return \ILIAS\UI\Component\Input\Field\Node\Node
     */
    public function node(
        string $id,
        string $name,
        ?Icon $icon = null,
        Node ...$children
    ): Node;

    /**
     *  ---
     *  description:
     *    purpose: >
     *      An async node is used by the tree select input and multi tree select input to visualise an
     *      entry inside the tree whose sub-nodes will be rendered asynchronously. This node consist
     *      of a unique identifier, name and optional icon. It can represent any kind of data.
     *    rivals:
     *      Node: >
     *          A node should be used if the sub-nodes of a node should not be rendered asynchronously
     *          on the client.
     *      Leaf Node: A leaf node should be used if the node cannot own any sub-nodes.
     *    rules:
     *       usage:
     *           1: >
     *              Icons SHOULD reflect the kind of data a node represents as closely as possible.
     *              If there is only one kind of data, an icon SHOULD NOT be provided.
     *  ---
     * @param string                                      $id
     * @param string                                      $name
     * @param \ILIAS\UI\Component\Symbol\Glyph\Glyph|null $icon
     * @return \ILIAS\UI\Component\Input\Field\Node\Async
     */
    public function async(string $id, string $name, Icon $icon = null): Async;

    /**
     *  ---
     *  description:
     *    purpose: >
     *      An leaf node is used by the tree select input and multi tree select input to visualise an
     *      entry inside the tree which cannot own any sub-nodes. This node consist of a unique identifier,
     *      name and optional icon. It can represent any kind of data.
     *    rivals:
     *      Node: A node should be used if the node cannot own any sub-nodes.
     *      Async Node: >
     *          A node should be used if the sub-nodes of a node should not be rendered asynchronously
     *          on the client.
     *    rules:
     *       usage:
     *           1: >
     *              Icons SHOULD reflect the kind of data a node represents as closely as possible.
     *              If there is only one kind of data, an icon SHOULD NOT be provided.
     *  ---
     * @param string                                      $id
     * @param string                                      $name
     * @param \ILIAS\UI\Component\Symbol\Glyph\Glyph|null $icon
     * @return \ILIAS\UI\Component\Input\Field\Node\Leaf
     */
    public function leaf(string $id, string $name, ?Icon $icon = null): Leaf;
}
