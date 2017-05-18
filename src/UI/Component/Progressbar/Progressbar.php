<?php
namespace ILIAS\UI\Component\Progressbar;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;

/**
 * Describes the Progressbar component
 */
interface Progressbar extends Component, Triggerable {

	/**
	 * @return int|null
	 */
	public function getPercentage();


	/**
	 *
	 * @return bool Returns true iff the progress bar should be animated.
	 */
	public function getActive();
}