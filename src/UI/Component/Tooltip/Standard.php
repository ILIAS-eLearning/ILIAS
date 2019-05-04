<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Tooltip;

use ILIAS\UI\Component\Component;

/**
 *
 */
interface Standard extends Tooltip
{
	/**
	 * @return Component[]
	 */
	public function contents(): array;
}