<?php

namespace ILIAS\UI\Component\Popover;

use \ILIAS\UI\Component\Component as Component;

/**
 * Factory to create different types of Popovers.
 */
interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *      Standard Popovers are used to display other components.
     *      Whenever you want to use the standard-popover, please hand in a PullRequest and discuss
     *      it.
     *   composition: >
     *      The content of a Standard Popover displays the components together with an optional title.
     * rivals: >
     *    Listing Popovers are used to display lists.
     * rules:
     *   usage:
     *      1: >
     *          Standard Popovers MUST NOT be used to render lists, use a Listing Popover
     *          for this purpose.
     *      2: Standard Popovers SHOULD NOT contain complex or large components.
     *      3: Usages of Standard Popovers MUST be accepted by JourFixe.
     *      4: >
     *          Popovers with fixed Position MUST only be attached to triggerers with
     *          fixed position.
     * ---
     *
     * @param Component|Component[] $content
     *
     * @return \ILIAS\UI\Component\Popover\Standard
     */
    public function standard($content);


    /**
     * ---
     * description:
     *   purpose: >
     *      Listing Popovers are used to display list items.
     *   composition: >
     *      The content of a Listing Popover displays the list together with an optional title.
     * rivals: >
     *   Standard Popovers display other components than lists.
     * rules:
     *   usage:
     *      1: Listing Popovers MUST be used if one needs to display lists inside a Popover.
     *      2: Popovers with fixed Position MUST only be attached to triggerers with fixed position.
     * ---
     *
     * @param Component[] $items
     *
     * @return \ILIAS\UI\Component\Popover\Listing
     */
    public function listing($items);
}
