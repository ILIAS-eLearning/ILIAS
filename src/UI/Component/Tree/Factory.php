<?php
declare(strict_types=1);

namespace ILIAS\UI\Component\Tree;

/**
 * Tree factory
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Nodes are entries in a Tree.
	 *   composition: >
	 *
	 * context:
	 *   - Nodes will only occur in Trees.
	 *
	 * rules:
	 *   usage:
	 *      1: X
	 *   accessibility:
	 *      1: X
	 *
	 * ---
	 * @return \ILIAS\UI\Component\Tree\Node\Factory
	 */
	public function node(): Node\Factory;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A Tree presents data in a hierarchically structured form.
	 *   composition: >
	 *     A Tree is composed of Nodes.
	 *
	 * rules:
	 *   usage:
	 *      1: X
	 *   accessibility:
	 *      1: X
	 *
	 * ---
	 * @param TreeRecursion $recursion
	 *
	 * @return \ILIAS\UI\Component\Tree\Expandable
	 */
	public function expandable(TreeRecursion $recursion): Expandable;

}
