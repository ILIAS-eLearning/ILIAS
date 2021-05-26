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
     *   purpose: >
     *     Toasts are temporary messages from the system published to the user.
     *     Toast Items are used to attract attention from a user without affecting the user experience permanent.
     *   composition: >
     *     Toast Items contain a title, a close button and an icon, which indicates the service or module
     *     triggering the toast. They might contain a description, a click action, which triggers when the user clicks
     *     the title of a toast, and a vanish action, which triggers when the toast vanished. Further the toast might
     *     contain a number of link items, which will be presented below the description.
     *   effect: >
     *     The item will be displayed overlaying the main content in the top right corner.
     *     If the user does not interact with the item it will vanish after a global configurable amount of time.
     *     If the item has a click action set, a click interaction of the user on the title will trigger this action.
     *     If the item has a vanish action set, the default vanishing (without user interaction) will trigger this action.
     *     A click interaction of the user on the close Glyph will prevent both other actions and the item will vanish.
     *
     * rules:
     *   interactions:
     *     1: >
     *       The click interaction offered by clicking on the Toast Items title SHOULD trigger the persisted click
     *       action.
     *     2: >
     *        Clicking on the Close Glyph MUST remove the Toast Item permanently..

     *   accessibility:
     *     1: >
     *       All interactions offered by a toast item MUST be accessible by using the mouse.
     * ---
     *
     * @param string $title Title of the item
     * @param Icon $icon lead icon
     *
     * @return Toast
     */
    public function standard(string $title, Icon $icon) : Toast;
}
