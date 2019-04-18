<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Tooltip;

use ILIAS\UI\Component\Component;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Tooltip
 * @author Niels Theen <ntheen@databay.de>
 * @author Colin Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Standard Tooltips are used to display other components.
	 *      Tooltips should give the users additional information about a
	 *      displayed component.
	 *      Tooltips can be used e.g. to give information for the correct
	 *      input value in a form or an displayed value in a view.
	 *   composition: >
	 *      The content of a Standard Tooltip displays the components.
	 *      A tooltip consists of a placement, ui components to be displayed
	 *      and the signal the tooltip reacts to.
	 * rules:
	 *   usage:
	 *      1: >
	 *          Standard Tooltips MUST have an array of \ILIAS\UI\Component\Component
	 * ---
	 *
	 * @param Component[] $contents An array of components that will be displayed in the tooltip.
	 * @return \ILIAS\UI\Component\Tooltip\Standard
	 */
	public function standard(array $contents): \ILIAS\UI\Component\Tooltip\Standard;
}
