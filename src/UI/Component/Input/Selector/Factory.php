<?php
namespace ILIAS\UI\Component\Input\Selector;
/**
 * Factory for Repository Selectors
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Repository Selectors pick a specific repository object to e.g. limit its application, to point to it, to become a starting point or the like.
	 *     They can pick exactly one repository object or multiple.
	 *   composition: >
	 *     Repository selectors contain of two main parts. One part shows the current selection with a triggers to change
	 *     this selction by opening part two, a modal containing the options in a tree to make a new selection from.
	 *   effect: >
	 *     The interaction slightly difers from selecting one to selecing multiple objects.
	 *     </br>-> If one object is selected, the user clicks the "Select"-Link in  the form. ILIAS calls a Roundtrip
	 *     Modal presenting the repository tree. User selects then the object by clicking on an object title (to pick one object).
	 *     ILIAS closes the modal and inserts the selected object into the form.
	 *     </br>-> If one multiple objects are to be selected, then the user also clicks the "Select"-Link in  the form. ILIAS calls a Roundtrip
	 *     Modal presenting the repository tree. User checks all checkboxes to pick more than one object and has to click
	 *     "Save" in order to complete the selction. ILIAS closes the modal and inserts the selected object into the form and adds
	 *     Add/Remove-Glyphs.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Repository Pickers MAY be used in Forms.
	 *
	 * ----
	 * @param @Todo define structure to pass tree
	 * @return  \ILIAS\UI\Component\Input\Selector\Repository
	 */
	public function repository();
}