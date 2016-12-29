<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      An Interruptive Modal disrupts the user in critical situation, forcing him or her to focus on the task at hand.
	 *   composition: >
	 *      The Modal states why this situation needs attention and may point out consequences.
	 *   effect: >
	 *      All controls of the original context are inaccessible until the Modal is completed. Upon completion the user
	 *      returns to the original context.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *        Due to the heavily disruptive nature of this type of Modal it MUST be restricted to critical situations (e.g. loss of data).
	 *     2: >
	 *        All delete-actions are critical situations and MUST be implemented as an Interruptive Modal.
	 *     3: >
	 *        Interruptive Modals MUST contain a Default Button continuing the action that initiated the Modal (e.g. Delete the Item) on the left
	 *        side of the footer of the modal and a button canceling the action on the right side of the footer as Default Button.
	 *     4: >
	 *        The Cancel Button in the footer and the Close Button in the header MUST NOT perform any additional action than closing
	 *        the interruptive modal.
	 * ---
	 *
	 * @param string              $title
	 * @param Component\Component $content
	 *
	 * @return \ILIAS\UI\Component\Modal\Interruptive
	 */
	public function interruptive($title, Component\Component $content);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Round-Trip Modals are to be used if the context would be lost by performing this action otherwise.
	 *     Round-Trip Modals accommodate sub-workflows within an overriding workflow. The Round-Trip Modal ensures that the users does
	 *     not leave the trajectory of the overriding workflow. This is typically the case if an ILIAS service is called for while
	 *     in working in an object.
	 *   composition: >
	 *     Round-Trip Modals are completed by a well defined sequence of only a few steps that might be displayed on a sequence of different
	 *     modals connected through some “next” Button.
	 *   effect: >
	 *     Round-Trip Modals perform sub-workflow involving some kind of user input. Sub-workflow is completed and user is returned
	 *     to starting point allowing for continuing the overriding workflow.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       Round-Trip Modals MUST contain at least two buttons at the bottom of the modals: a button to cancel (right) the workflow
	 *       and a button to finish or reach the next step in the workflow (left).
	 *     2: >
	 *       Round-Trip Modals SHOULD be used, if the user would lose the context otherwise. If the action can be performed within
	 *       the same context (e.g. add a post in a forum, edit a wiki page), a Round-Trip Modal MUST NOT be used.
	 *     3: >
	 *       When the workflow is completed, Round-Trip Modals SHOULD show the same view as was displayed when initiating the Modal.
	 *     4: >
	 *       Round-Trip Modals SHOULD NOT be used to add new items of any kind since adding item is a linear workflow redirecting to the
	 *       newly added item setting- or content-tab.
	 *     5: >
	 *       Round-Trip Modals SHOULD NOT be used to perform complex workflows.
	 *
	 * ---
	 * @param string              $title
	 * @param Component\Component $content
	 *
	 * @return \ILIAS\UI\Component\Modal\RoundTrip
	 */
	public function roundtrip($title, Component\Component $content);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Lightbox Modal displays a full of data such as documents, images or a movie inside a Lightbox.
	 *   composition: >
	 *     If multiple pages or images are to be displayed in one Lightbox Modal, they can flipped through.
	 *   effect: >
	 *      Lightbox Modals are activated by clicking the Full View Glyphicon, the Title of the object or it’s Thumbnail.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       Lightobx Modals MUST contain a title above the presented item.
	 *     2: >
	 *       Lightbox Modals SHOULD contain a descriptional text below the presented items.
	 *     3: >
	 *       Multiple images or pages inside a Lightbox Modals MUST be presented in carousel like manner allowing to flickr through items.
	 *
	 * ---
	 * @param string              $title
	 * @param Component\Component $content
	 *
	 * @return \ILIAS\UI\Component\Modal\Lightbox
	 */
	public function lightbox($title, Component\Component $content);
}
