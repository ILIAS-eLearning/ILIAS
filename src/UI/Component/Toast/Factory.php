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
     *     triggering the Toast. They might contain a description, a click action, and a vanish action, which is triggered
     *     when the Toast is vanished. Further the Toast might contain a number of ILIAS Link components, which will be
     *     presented below the description.
     *   effect:
     *     The item will be displayed overlaying the main content.
     *     If the item has a vanish action set, the default vanishing (without user interaction) will trigger this
     *     action (The response of this interaction will not be displayed).
     *     A click interaction of the user on the close Glyph will prevent both other actions and the item will vanish.
     * rules:
     *   style:
     *     1: The Toast SHOULD be limited in space so it does not cover all of the pages content, no matter the size of its own content.
     *     2: The description of the Toast MUST not render any non-textual context (e.g. HTML).
     *   ordering:
     *     1: A new Toast SHOULD be ordered below all existing Toasts.
     *   responsiveness:
     *     1: The Toast SHOULD always have the same size on full display and be independent from the display size.
     *     2: The Toast MAY be hidden on devices with a low screen size.
     * ---
     *
     * @param string|\ILIAS\UI\Implementation\Component\Button\Shy|\ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link $title Title of the item
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon $icon lead icon
     *
     * @return  \ILIAS\UI\Component\Toast\Toast
     */
    public function standard($title, Icon $icon) : Toast;
}
