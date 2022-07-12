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
 * Interface for mapping data-structures to the Tree.
 * The Tree is configured with a not further defined set of data. This data
 * MUST be iterable, i.e. an array; a Node is build from each entry (=record)
 * via TreeRecursion::build.
 * A record MAY provide further children/sub-structures, again, not further
 * specified. Therefore, potential children are retrieved by TreeRecursion::getChildren
 * called with the respective record.
 * Additionally, a Tree can be configured with an $environment, which can be virtually
 * anything that is usefull or required for the proper construction of nodes (or identifying children).
 * For example, if you want to present certain nodes depending on the user's permissions,
 * you should use something like "$env['ilaccess'] = $DIC['ilAccess'];" and pass $env to
 * the implementation of TreeRecursion.
 * Please refer to the examples in src/UI/examples/Tree to see how this works.
 */
interface TreeRecursion
{
    /**
     * Get a list of records (that list can also be empty).
     * Each record will be relayed to $this->build to retrieve a Node.
     * Also, each record will be asked for Sub-Nodes using this function.
     */
    public function getChildren(
        $record,
        $environment = null
    ) : array;

    /**
     * Build and return a Node.
     * The renderer will provide the $factory-parameter which is the UI-factory
     * for nodes, as well as the (unspecified) $environment as configured at the Tree.
     * $record is the data the node should be build for.
     */
    public function build(
        Node\Factory $factory,
        $record,
        $environment = null
    ) : Node\Node;
}
