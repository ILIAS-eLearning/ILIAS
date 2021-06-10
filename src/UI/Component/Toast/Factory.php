<?php

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
     *     Standard Toasts display a normal toast in the top right corner of the given context.
     *   composition:
     *     Standard Toasts contain a title, a close button and an icon, which indicates the service or module
     *     triggering the Toast. They might contain a description, a click action, which triggers when the user clicks
     *     the title of a Toast, and a vanish action, which triggers when the Toast vanished. Further the Toast might
     *     contain a number of link items, which will be presented below the description.
     *   effect:
     *     The item will be displayed overlaying the main content in the top right corner.
     *     If the user does not interact with the item it will vanish after a global configurable amount of time.
     *     If the item has a click action set, a click interaction of the user on the title will trigger this action.
     *     If the item has a vanish action set, the default vanishing (without user interaction) will trigger this
     *     action.
     *     A click interaction of the user on the close Glyph will prevent both other actions and the item will vanish.
     * rules:
     *   interaction:
     *     1: The click interaction offered by clicking on the Toast Items title SHOULD trigger the persisted click
     *        action.
     *   style:
     *     1: The Toast SHOULD be limited in space to less than width of 500px, no matter the size of its content.
     *     2: The description of the Toast MUST not render any non-textual context (e.g. HTML).
     *   ordering:
     *     1: A new Toast SHOULD be ordered below all existing Toasts.
     *   responsiveness:
     *     1: The Toast SHOULD always have the same size on full display and be independent from the display size.
     *     2: The Toast MAY be hidden on devices with a low screen size.
     *   accessibility:
     *     1: All interactions offered by a Toast item MUST be accessible by using the mouse or touchscreen.
     * ---
     *
     * @param string $title Title of the item
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon $icon lead icon
     *
     * @return  \ILIAS\UI\Component\Toast\Toast
     */
    public function standard(string $title, Icon $icon) : Toast;
}
