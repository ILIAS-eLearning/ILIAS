<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Tree;

use \ILIAS\UI\Component\Component;

/**
 * This describes a Tree Control
 */
interface Tree extends Component
{
	/**
	 * Configure the Tree with additional information that will be
	 * relayed to TreeRecursion.
	 */
	public function withEnvironment($environment): Tree;

	/**
	 * Apply data to the Tree.
	 */
	public function withData($data): Tree;

	/**
	 * Get the environment.
	 */
	public function getEnvironment();

	/**
	 * Get the data.
	 */
	public function getData();

	/**
	 * Get the mapping-class.
	 */
	public function getRecursion(): TreeRecursion;

	/**
	 * Should a clicked node be highlighted?
	 */
	public function withHighlightOnNodeClick(bool $highlight): Tree;

	/**
	 * Is the tree configured to highlight a clicked node?
	 */
	public function getHighlightOnNodeClick(): bool;

}
