<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Tree\Node;

use ILIAS\Data\URI;
use \ILIAS\UI\Component\Component;
use \ILIAS\UI\Component\Clickable;

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
     *
     * @return URI
     */
    public function getLink(): URI;

    /**
     * Create a new node object with an URI that will be added to the UI
     * @param URI $uri
     * @return Node
     */
    public function withLink(URI $uri): Node;

}
