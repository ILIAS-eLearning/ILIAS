<?php
declare(strict_types=1);

namespace ILIAS\UI\Component\Tree\Node;

/**
 * Nodes factory
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *
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
	 *
	 * @return \ILIAS\UI\Component\Tree\Node\Simple
	 */
	public function simple(string $label): Simple;

}
