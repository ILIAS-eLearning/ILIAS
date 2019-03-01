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
	 * @return \ILIAS\UI\Component\Tree\Tree
	 */
	public function tree(TreeRecursion $recursion): Tree;

}
