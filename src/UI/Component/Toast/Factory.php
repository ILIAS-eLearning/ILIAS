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
 
namespace ILIAS\UI\Component\Toast;

use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * This is how a factory for Toast looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose:
     *     Standard Toasts display a normal toast inside a toast container.
     *   composition:
     *     Standard Toasts contain a title, a close button and an icon, which indicates the service or module
     *     triggering the Toast. They might contain a description and an action, which is triggered
     *     when user interact with the Toast. Further the Toast might contain a number of ILIAS Link components, which
     *     will be presented below the description.
     *   effect:
     *     If the item has an action set, a click interaction of the user with the Toast will trigger this
     *     action (The response of this interaction will not be displayed).
     * context:
     *   - The Toast should only be used inside a toast container.
     * rules:
     *   style:
     *     1: The description of the Toast MUST not render any non-textual context (e.g. HTML).
     *   ordering:
     *     1: A new Toast SHOULD be ordered below all existing Toasts of its surrounding toast container.
     *   responsiveness:
     *     1: The Toast SHOULD always have the same size on full display and be independent from the display size.
     * ---
     *
     * @param string|\ILIAS\UI\Implementation\Component\Button\Shy|\ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link $title Title of the item
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon $icon lead icon
     *
     * @return  \ILIAS\UI\Component\Toast\Toast
     */
    public function standard($title, Icon $icon) : Toast;

    /**
     * ---
     * description:
     *   purpose:
     *     Toasts Containers display Toasts on the ILIAS page.
     *     It has no visual appearance on it's own but provides a location for the Toasts.
     *   composition:
     *     Toast Containers contain a group of toasts.
     *   effect:
     *     The container will be displayed overlaying the main content.
     *     If the container is empty it will not be displayed.
     * rules:
     *   style:
     *     1: The Toast Container SHOULD be limited in space so it does not cover all of the pages content, no matter
     *        the size of its own content.
     *     2: An empty Toast Container SHOULD have no effect on the visible page.
     *   accessibility:
     *     1: All Toast Containers MUST alert screen readers when appearing and therefore MUST declare the role "alert" or
     *        aria-live.
     * ---
     *
     * @return  \ILIAS\UI\Component\Toast\Container
     */
    public function container() : Container;
}
