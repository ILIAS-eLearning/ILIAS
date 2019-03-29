<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Tooltip;

use ILIAS\UI\Component\Component;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Tooltip
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Standard Tooltips are used to display other components.
	 *      Whenever you want to use the standard-tooltip, please hand in a PullRequest and discuss
	 *      it.
	 *   composition: >
	 *      The content of a Standard Tooltip displays the components.
	 * rules:
	 *   usage:
	 *      1: >
	 *          Standard Tooltips ...
	 * ---
	 *
	 * @param Component[]
	 * @return \ILIAS\UI\Component\Tooltip\Standard
	 */
	public function standard(array $contents): \ILIAS\UI\Component\Tooltip\Standard;
}