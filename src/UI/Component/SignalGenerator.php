<?php
namespace ILIAS\UI\Component;

/**
 * Interface SignalGenerator
 *
 * @package ILIAS\UI\Component
 */
interface SignalGenerator {

	/**
	 * Create a unique signal
	 *
	 * @return string
	 */
	public function create();

}