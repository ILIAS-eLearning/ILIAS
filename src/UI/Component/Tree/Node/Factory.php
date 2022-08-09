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
 
namespace ILIAS\UI\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Nodes factory
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Simple Node is a very basic entry for a Tree.
     *   composition: >
     *     It consists of a string-label, an optional Icon and an optional URI.
     *   effect: >
     *     The Simple Node can be configured with an URL to load
     *     data asynchronously. In this case, before loading there is always
     *     an Expand Glyph in front of the Node.
     *     If there are no further levels, the Expand Glyph will disappear
     *     after loading.
     *     Furthermore, SimpleNode implements Clickable and can be configured to
     *     trigger an action.
     * rules:
     *   usage:
     *      1: >
     *        A Simple Node SHOULD be used when there is no need to relay
     *        further information for the user to choose. This is the case
     *        for most occurrences where repository-items are shown.
     * ---
     * @param string                                    $label
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon|null $icon
     * @param URI|null                                  $uri
     * @return \ILIAS\UI\Component\Tree\Node\Simple
     */
    public function simple(string $label, Icon $icon = null, URI $uri = null) : Simple;

    /**
     * ---
     * description:
     *   purpose: >
     *     The Bylined Node is an entry containing additional information about
     *     the node.
     *   composition: >
     *     It consists of a string-label, a byline and an optional Icon.
     *   effect: >
     *     This node is a simple node with an additional string-byline.
     * rules:
     *   usage:
     *      1: >
     *        A Byline Node SHOULD be used when there is a need to display a
     *        byline of additional information to a tree node.
     * ---
     * @param string                                    $label
     * @param string                                    $byline
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon|null $icon
     * @return \ILIAS\UI\Component\Tree\Node\Bylined
     */
    public function bylined(string $label, string $byline, Icon $icon = null) : Bylined;

    /**
     * ---
     * description:
     *   purpose: >
     *     The Paired Node is an entry containing a value paired to its label,
     *     to better distinguish it from other nodes or clarify its function.
     *   composition: >
     *     It consists of a string-label complemented by additional string as a
     *     key-value pair, and an optional Icon.
     *   effect: >
     *     This node is a simple node with an additional string-value behind
     *     the label.
     * rules:
     *   usage:
     *      1: >
     *        A Paired Node SHOULD be used when additional information besides
     *        the label is needed to adequately identify a tree node and its
     *        function.
     * ---
     * @param string                                    $label
     * @param string                                    $byline
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon|null $icon
     * @return \ILIAS\UI\Component\Tree\Node\Paired
     */
    public function paired(string $label, string $value, Icon $icon = null) : Paired;
}
