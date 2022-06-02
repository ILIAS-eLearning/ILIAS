<?php declare(strict_types=1);

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
     * ---
     * @return \ILIAS\UI\Component\Tree\Node\Factory
     */
    public function node() : Node\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *     An Expandable Tree focuses on the exploration of hierarchically
     *     structured data. Its nodes can be expanded to reveal the underlying
     *     nodes; nodes in the Expandable Tree can also be closed to hide all
     *     underlying nodes. This lets the user decide on the simultaneously
     *     shown levels of the data's hierarchy.
     *   composition: >
     *     A Tree is composed of Nodes.
     *     Further, levels (sub-Nodes) are indicated by an Expand Glyph
     *     for the closed state of the Node and respectively by a Collapse Glyph
     *     for the expanded state.
     *     If there are no sub-Nodes, no Glyph will be shown at all. It is possible
     *     to only render a part of a tree and load further parts on demand.
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
     *   accessibility:
     *     1: Expandable Trees MUST bear the ARIA role "tree".
     *     2: The "aria-label" attribute MUST be set for Expandable Trees.
     *     3: The "aria-label" attribute MUST be language-dependant.
     *     4: >
     *        The "aria-label" attribute MUST describe the content of the Tree as
     *        precisely as possible. "Tree" MUST NOT be set as label, labels like
     *        "Forum Posts" or "Mail Folders" are much more helpful.
     *        (Note that "Tree" is already set by the ARIA role attribute.)
     *     5: >
     *        Every Node in der Tree MUST be accessible by keyboard. Note they this does not imply, that all Nodes
     *        are tabbable.
     *     6: At least Node in the tree MUST be tabbable.
     * ---
     * @param string $label
     * @param TreeRecursion $recursion
     * @return \ILIAS\UI\Component\Tree\Expandable
     */
    public function expandable(string $label, TreeRecursion $recursion) : Expandable;
}
