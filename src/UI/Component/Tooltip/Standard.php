<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Tooltip;

use ILIAS\UI\Component\Component;

/**
 * Interface Standard
 * @package ILIAS\UI\Component\Tooltip
 * @author Niels Theen <ntheen@databay.de>
 * @author Colin Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Standard extends Tooltip
{
	/**
	 * @return Component[]
	 */
	public function contents(): array;
}