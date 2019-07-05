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
	 *     Nodes are entries in a Tree. They represent a level in the Tree's
	 *     data hierarchy.
	 *
	 * context:
	 *   - Nodes will only occur in Trees.
	 *
	 * rules:
	 *   usage:
	 *     1: Nodes MUST only be used in a Tree.
	 *     2: >
	 *       Nodes SHOULD NOT be constructed with subnodes. This is the job
	 *       of the Tree's recursion-class.
	 *   style:
	 *     1: >
	 *       Nodes MUST restrict themselves to a minimal presentation, i.e.
	 *       they MUST solely display information supportive and relevant for
	 *       the intended task.
	 *
	 * ---
	 * @return \ILIAS\UI\Component\Tree\Node\Factory
	 */
	public function node(): Node\Factory;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     An Expandable Tree focusses on the exploration of hierarchically
	 *     structured data. Its nodes can be expanded to reveal the underlying
	 *     nodes; nodes in the Expandable Tree can also be closed to hide all
	 *     underlying nodes. This lets the user decide on the simultaneously
	 *     shown levels of the data's hierarchy.
	 *   composition: >
	 *     A Tree is composed of Nodes.
	 *     Further levels (sub-Nodes) are indicated by an Expand Glyph
	 *     for the closed state of the Node and respectively by a Collapse Glyph
	 *     for the expanded state.
	 *     If there are no sub-Nodes, no Glyph will be shown at all.
	 *   effect: >
	 *     When clicking a Node, it will expand or collapse, thus showing or hiding
	 *     its sub-Nodes.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *        Expandable Trees SHOULD only be used when there is a reasonably (large)
	 *        amount of entries.
	 *     2: >
	 *        Expandable Trees SHOULD NOT be used to display several aspects of one
	 *        topic/item, like it would be the case when e.g. listing a repository
	 *        object and its properties as individual nodes.
	 *
	 * ---
	 * @param TreeRecursion $recursion
	 *
	 * @return \ILIAS\UI\Component\Tree\Expandable
	 */
	public function expandable(TreeRecursion $recursion): Expandable;

}
