<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Tooltip;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;

/**
 * Interface Tooltip
 * @package ILIAS\UI\Component\Tooltip
 */
interface Tooltip extends Component, Triggerable
{
	const PLACEMENT_TOP = 'top';
	const PLACEMENT_RIGHT = 'top';
	const PLACEMENT_BOTTOM = 'bottom';
	const PLACEMENT_LEFT = 'left';

	/**
	 * @return Signal
	 */
	public function getShowSignal(): Signal;

	/**
	 * @return self
	 */
	public function withPlacementTop(): self;

	/**
	 * @return self
	 */
	public function withPlacementRight(): self;

	/**
	 * @return self
	 */
	public function withPlacementLeft(): self;

	/**
	 * @return self
	 */
	public function withPlacemenBottom(): self;

	/**
	 * @return string
	 */
	public function getPlacement(): string;
}