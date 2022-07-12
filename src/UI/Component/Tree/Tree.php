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

use ILIAS\UI\Component\Component;

/**
 * This describes a Tree Control
 */
interface Tree extends Component
{
    /**
     * Configure the Tree with additional information that will be
     * relayed to TreeRecursion.
     */
    public function withEnvironment($environment) : Tree;

    /**
     * Get the (aria-)label
     */
    public function getLabel() : string;

    /**
     * Apply data to the Tree.
     */
    public function withData($data) : Tree;

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
    public function getRecursion() : TreeRecursion;

    /**
     * Should a clicked node be highlighted?
     */
    public function withHighlightOnNodeClick(bool $highlight) : Tree;

    /**
     * Is the tree configured to highlight a clicked node?
     */
    public function getHighlightOnNodeClick() : bool;

    /**
     * Is this only a part of a tree? Needed if parts are loaded async
     */
    public function isSubTree() : bool;

    /**
     * Set this tree to be a part of a tree. Needed if parts are loaded async.
     */
    public function withIsSubTree(bool $is_sub) : Tree;
}
