<?php
namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component\Signal;

/**
 * Interface SignalGeneratorInterface
 *
 * @package ILIAS\UI\Component
 */
interface SignalGeneratorInterface {

	/**
	 * Create a unique signal
	 *
	 * @return Signal
	 */
	public function create();

}