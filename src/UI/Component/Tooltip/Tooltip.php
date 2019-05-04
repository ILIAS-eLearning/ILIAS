<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Tooltip;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;

/**
 *
 */
interface Tooltip extends Component, Triggerable
{
	const POSITION_AUTO = 'auto';
	const POSITION_TOP = 'top';
	const POSITION_RIGHT = 'right';
	const POSITION_BOTTOM = 'bottom';
	const POSITION_LEFT = 'left';

	/**
	 * Get the signal the tooltip reacts to.
	 *
	 * @return Signal
	 */
	public function getShowSignal(): Signal;

	/**
	 * Create a new instance of the tooltip with a position on the `top` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withTopPosition(): self;

	/**
	 * Create a new instance of the tooltip with a position on the `right` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withRightPosition(): self;

	/**
	 * Create a new instance of the tooltip with a position on the `left` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withLeftPosition(): self;

	/**
	 * Create a new instance of the tooltip with a position on the `bottom` of
	 * the current UI element.
	 *
	 * @return self
	 */
	public function withBottomPosition(): self;

	/**
	 * Create a new instance of the tooltip with default/automatic positioning.
	 *
	 * @return self
	 */
	public function withAutomaticPosition(): self;

	/**
	 * Get the current position of the tooltip as string.
	 * The value is based on the Constants of the interface
	 * `UI\Component\Tooltip\Tooltip`.
	 *
	 * @return string
	 */
	public function getPosition(): string;
}
