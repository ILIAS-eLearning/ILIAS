<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Tooltip;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;

/**
 * Interface Tooltip
 * @package ILIAS\UI\Component\Tooltip
 * @author Niels Theen <ntheen@databay.de>
 * @author Colin Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Tooltip extends Component, Triggerable
{
	const PLACEMENT_TOP = 'top';
	const PLACEMENT_RIGHT = 'top';
	const PLACEMENT_BOTTOM = 'bottom';
	const PLACEMENT_LEFT = 'left';

	/**
	 * Get the signal the tooltip reacts to.
	 *
	 * @return Signal
	 */
	public function getShowSignal(): Signal;

	/**
	 * Create a new instance of the tooltip with an placement on the `top` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withPlacementTop(): self;

	/**
	 * Create a new instance of the tooltip with an placement on the `right` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withPlacementRight(): self;

	/**
	 * Create a new instance of the tooltip with an placement on the `left` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withPlacementLeft(): self;

	/**
	 * Create a new instance of the tooltip with an placement on the `bottom` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withPlacemenBottom(): self;

	/**
	 * Get the current placement of the tooltip as string.
	 * The value is based on the Constants of the interface
	 * `UI\Component\Tooltip\Tooltip`.
	 *
	 * @return string
	 */
	public function getPlacement(): string;
}
