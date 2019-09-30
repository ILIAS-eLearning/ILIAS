<?php

namespace ILIAS\UI\Component\MainControls\Slate;

/**
 * This is what a factory for slates looks like.
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Legacy Slate is used to wrap content into a slate when there is
	 *     no other possibility (yet). In general, this should not be used and
	 *     may vanish with the progress of specific slates.
	 *
	 *   composition: >
	 *     The Legacy Slate will take a Legacy-Component and render it.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       This component MUST NOT be used to display elements that can be
	 *       generated using other UI Components.
	 *
	 * ----
	 *
	 * @param string $name
	 * @param \ILIAS\UI\Component\Symbol\Symbol $symbol
	 * @param \ILIAS\UI\Component\Legacy\Legacy $content
	 * @return \ILIAS\UI\Component\MainControls\Slate\Legacy
	 */
	public function legacy(
		string $name,
		\ILIAS\UI\Component\Symbol\Symbol $symbol,
		\ILIAS\UI\Component\Legacy\Legacy $content
	): Legacy;


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Combined Slate bundles related controls; these can also be further
	 *     Slates. Combined Slates are used when a specific purpose is being
	 *     subdivided into further aspects.
	 *
	 *   composition: >
	 *     The Combined Slate consists of more Slates and/or Bulky Buttons.
	 *     The symbol and name of contained Slates are turned into a Bulky Button
	 *     to control opening and closing the contained Slate.
	 *
	 *   effect: >
	 *     Opening a Combined Slate will display its contained Slates with an
	 *     operating Bulky Button for closing/expanding.
	 *     Clicking on a Button not connected to a Slate will carry out its action.
 	 *
	 * context:
	 *   - The Combined Slate is used in the Main Bar.
	 *
	 * ----
	 *
	 * @param string $name
	 * @param \ILIAS\UI\Component\Symbol\Symbol $symbol
	 * @return \ILIAS\UI\Component\MainControls\Slate\Combined
	 */
	public function combined(
		string $name,
		\ILIAS\UI\Component\Symbol\Symbol $symbol
	): Combined;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Notifications Slates are used by the system to publish information to the user in the form of Notification Items.
	 *     The aim of the Notification Slates and the Notification Items they contain, is to make notifications
	 *     visible and quickly accessible. They form a centralized channel into which notifications are bundled into.
	 *     Note that the Notification Slates and Items do not replace the short-lived message displayed on the screen without
	 *     page loading (like "You have received 1 Contact Request") currently called "toasts".
	 *
	 *   composition: >
	 *     Notifications Slates hold Notification Items, displaying information on and possible interactions
	 *     with the displayed Notifications. They display the Notification Items chronological order (with the latest on top).
	 *     Each Notification Slate bundles Notification Items of one specific type of source (service, e.g. Mail).
	 *
	 *   effect: >
	 *     By default Notification Slates are engaged, meaning, they display there content to the user.
	 *
	 *   rivals:
	 *      Combined Slates: >
	 *          Combined Slates can hold Bulky Links and other Slates, Notification Slates may only contain
	 *          Notification Items. Further Combined Slates always require an icon and the contained slates are by
	 *          default dis-engaged.
	 *      Item Group: >
	 *          Item Groups bundle any kind of Items, may hold actions on those Items and do not feature an disengaged State.
	 *
	 * context:
	 *   - Notifications in the Meta Bar
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       Every service that can send a notification SHOULD add an entry in the Notification Center.
	 *     2: >
	 *       The displayed Notifications also SHOULD have a permanent place (mainly in Main Bar),
	 *       somewhere where old messages shown as Notification Item can still be viewed, even if they are removed
	 *       from the Notification Slate. Exceptions to this are the chat and the Background Tasks.
	 *   composition:
	 *     1: >
	 *          Each Notification Slate MUST bundle Notification Items of one specific type of source (service, e.g. Mail).
	 *     2: >
	 *          Notification Slates MUST NOT be empty.
	 *   ordering:
	 *       1: >
	 *          Notification Items displayed inside the Notification Slate MUST be displayed in chronological
	 *          order where the newest item MUST be the topmost.
	 *
	 * ----
	 *
	 * @param string $name
	 * @param \ILIAS\UI\Component\Item\Notification[] $notification_items
	 * @return \ILIAS\UI\Component\MainControls\Slate\Notification
	 */
	public function notification(string $name, array $notification_items): Notification;
}
