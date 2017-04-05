<?php
namespace ILIAS\UI\Component\Input;
/**
 * This is what a factory for inputs looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Ratings allow the user to pick a number from a fix scale of five to express valuation.
	 *     Each (full-number) position on the scale has a term assigned that "translates"
	 *     the numerical value into a colloquial term.
	 *
	 *   composition: >
	 *     A rating-input consists of five identical graphical elements that can be selected/clicked
	 *     and a string that is displayed dependent on the selected/hovered item.
	 *     Besides the title a byline explaining the topic to be rated can be displayed.
	 *     In order to show the amount of previously received ratings a counter can be used.
	 *     The average of previously submitted ratings can be visualized by a horizontal bar.
	 *     Byline, counter and average are optional.
	 *
	 *   effect: >
	 *     A RatingSelector-input displays five stars in a horizontal row.
	 *     By selecting one position, the star at that position is highlighted as well as
	 *     all stars left to it, such rather expressing an amount than a position.
	 *     A term specifying/explaining the selected value is displayed.
	 *     The probably most famous rating input is the amazon five-star rating.
	 *
	 * rules:
	 *   usage:
	 *       1: Ratings SHOULD appear close to the thing that is rated.
	 *       2: Ratings MAY be used in forms.
	 *   composition:
	 *       1: Ratings MUST have a topic.
	 *       2: Ratings SHOULD use captions for the scale.
	 *
	 * ----
	 *
	 * @param 	string 		$topic
	 * @return  \ILIAS\UI\Component\Input\Rating\Rating
	 */
	public function rating($topic);

}
