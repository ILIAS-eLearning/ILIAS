<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Tree;

/**
 * Interface for mapping data-structures to the Tree.
 * The Tree is configured with a not further defined set of data.
 */
interface TreeRecursion
{
	/**
	 * Get a list of records.
	 * Each record will be relayed to $this->build to retrieve a Node.
	 * Also, each record will be asked for Sub-Nodes using this function.
	 * @return array
	 */
	public function getChildren($data): array;

	/**
	 * Build and return a Node.
	 * @return Node
	 */
	public function build(Node\Factory $factory, $record): Node\Node;

}
