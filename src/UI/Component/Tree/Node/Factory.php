<?php
declare(strict_types=1);

namespace ILIAS\UI\Component\Tree\Node;

use ILIAS\UI\Component\Icon\Icon;

/**
 * Nodes factory
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Simple Nodes is the most basic entry in a Tree.
	 *   composition: >
	 *
	 *   effect: >
	 *
	 * rules:
	 *   usage:
	 *      1: X
	 *   accessibility:
	 *      1: X
	 *
	 * ---
	 * @param string $label
	 * @param \ILIAS\UI\Component\Icon\Icon|null $icon
	 *
	 * @return \ILIAS\UI\Component\Tree\Node\Simple
	 */
	public function simple(string $label, Icon $icon=null): Simple;

}
