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
     *     Toasts are temporary messages from the system published to the user.
     *     Toast Items are used to attract attention from a user without affecting the user experience permanent.
     *   composition:
     *     Toast Items contain a title, a close button and an icon, which indicates the service or module
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
     *   rivals:
     *     1: The Toast is similar to the OSD notifcation, which arent a component ATM(26.04.2021). Therefore it suppose to
     *     replace and unify this UI violation.
     * rules:
     *   usage:
     *     1: The Toast MUST be used for all Notifications which COULD require the users awareness in the moment the
     *        are created.
     *     2: The Toast MUST NOT be used for Notifications which are not time relevant to the point of their creation.
     *   composition:
     *     1: The Toast SHOULD precede all Notifications which are relevant for the user in time.
     *   interactions:
     *     1: The click interaction offered by clicking on the Toast Items title SHOULD trigger the persisted click
     *        action.
     *     2: Clicking on the Close Glyph MUST remove the Toast Item permanently.
     *   style:
     *     1: The Toast MUST be visible on the top layer of the page, Therefore it MUSt cover up all other UI Items in
     *        its space.
     *     2: The Toast SHOULD be limited in space to less than width of 500px, no matter the size of its content.
     *     3: The Toast MUST disappear after a certain amount of time or earlier by user interaction. No interaction can
     *        extends the Toast time of appearance above the global defined amount.
     *     4: The description of the Toast MUST not render any non-textual context (e.g. HTML).
     *   ordering:
     *     1: A new Toast SHOULD be ordered below all existing Toasts.
     *   responsiveness:
     *     1: The Toast SOULD always have the same size on full display and be independent from the display size.
     *     2: The Toast MAY be hidden on devices with a low screen size.
     *   accessibility:
     *     1: All interactions offered by a Toast item MUST be accessible by using the mouse or touchscreen.
     *     2: All interactions SHOULD be only accessible as long a the Toast is not vanished.
     * ---
     *
     * @param string $title Title of the item
     * @param Icon $icon lead icon
     *
     * @return Toast
     */
    public function standard(string $title, Icon $icon) : Toast;
}
