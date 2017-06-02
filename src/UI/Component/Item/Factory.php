<?php
namespace ILIAS\UI\Component\Listing;
/**
 * This is how a factory for Items looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The Appointment Item is used to summarize one single
	 *      appointment in a list.
	 *   composition: >
	 *      The Appointment Item is composed of a period of time, indicating
	 *      when this appointment takes place, a title, and a color, indicating which
	 *      calendar holds this appointment.
	 *      They further might contain a description and some properties as
	 *      key-value pair holding information such as location or contact.
	 *      If there are actions possible to perform on the appointment they
	 *      are listed in a dropdown on the right.
	 *   effect: >
	 *      The title may open the details of the appointment on click in a
	 *      round-trip modal, if such details are available.
	 *      The description is blended out if larger than two lines.
	 *      On small screen sizes, the description and properties (except for the
	 *      location) is blended out completely. It is shown once the user
	 *      clicks the "Show More" link displayed if there is hidden content.
	 * ---
	 * @param string $title Title of the Appointment
	 * @param ilDateTime $from Starting point of the appointment.
	 * @param ilDateTime $to End point of the appointment.
	 * @param string Color of the calendar containing the item as color code
	 *        (hex).
	 * @return \ILIAS\UI\Component\Item\Appointment
	 */
	public function appointment($title, \ilDateTime $from, \ilDateTime $to, $color);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       TODO, this is a further candidate to make use of Items. This could be
	 *       stuffed with items of no-specific type.
	 * ---
	 * @return \ILIAS\UI\Component\Item\Standard
	 */
	public function standard();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       TODO, this is a further candidate to make use of Items.
	 * ---
	 * @return \ILIAS\UI\Component\Item\Repository
	 */
	public function repository();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       TODO, this is a further candidate to make use of Items.
	 * ---
	 * @return \ILIAS\UI\Component\Item\BlogPosting
	 */
	public function blogPosting();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       TODO, this is a further candidate to make use of Listing Panels
	 * ---
	 * @return \ILIAS\UI\Component\Item\ForumPosting
	 */
	public function forumPosting();
}
