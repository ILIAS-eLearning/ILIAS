<?php
namespace ILIAS\UI\Component\Layout;
/**
 * This is what a factory for layouts looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A page-element describes a section or subsection of the ILIAS Application;
	 *     the page thus is the total view upon ILIAS.
	 *
	 *
	 * ----
	 *
	 * @return  \ILIAS\UI\Component\Layout\Page\Factory
	 */
	public function page();

}
