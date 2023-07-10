<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Clickable;

/**
 * This describes a Tree Node
 */
interface Node extends Component, Clickable
{
    /**
     * Get the label of this Node.
     */
    public function getLabel(): string;

    /**
     * Add a Node under this one.
     */
    public function withAdditionalSubnode(Node $node): Node;

    /**
     * Get all Nodes under this one.
     * @return Node[]
     */
    public function getSubnodes(): array;

    /**
     * Set $expanded to true to have this node expanded on loading.
     */
    public function withExpanded(bool $expanded): Node;

    /**
     * Should this Node be expanded on loading?
     */
    public function isExpanded(): bool;

    /**
     * Set $highlighted to true to have this node highlighted on loading.
     */
    public function withHighlighted(bool $expanded): Node;

    /**
     * Should this Node be highlighted on loading?
     */
    public function isHighlighted(): bool;

    /**
     * Get the URI object that is added as link in the UI
     */
    public function getLink(): ?URI;

    /**
     * Create a new node object with an URI that will be added to the UI
     */
    public function withLink(URI $link): Node;
}
