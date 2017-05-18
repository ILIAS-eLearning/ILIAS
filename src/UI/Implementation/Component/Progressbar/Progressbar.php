<?php

namespace ILIAS\UI\Implementation\Component\Progressbar;

use \ILIAS\UI\Component;

class Progressbar implements Component\Progressbar\Progressbar {

	/**
	 * @var int
	 */
	protected $percentage = null;
	/**
	 * @var int
	 */
	protected $active;

	public function __construct($percentage = null, $active = true) {
		$this->percentage = $percentage;
		$this->active = $active;
	}

	/**
	 * Get a component like this but reset (regenerate) its signals.
	 *
	 * @return $this
	 */
	public function withResetSignals() {
		// TODO: Implement withResetSignals() method.
	}


	/**
	 * @return null
	 */
	public function getPercentage() {
		return $this->percentage;
	}

	/**
	 *
	 * @return bool Returns true iff the progress bar should be animated.
	 */
	public function getActive() {
		return $this->active;
	}
}