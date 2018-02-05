<?php
namespace ILIAS\UI\Implementation\Component;

//Cannot use ILIAS\UI\Component\Signal as Signal because the name is already in use in
// /Users/leifos/Sites/ILIAS/src/UI/Implementation/Component/SignalGeneratorInterface.php:5
use ILIAS\UI\Component\Signal as Sig;

/**
 * Interface SignalGeneratorInterface
 *
 * @package ILIAS\UI\Component
 */
interface SignalGeneratorInterface {

	/**
	 * Create a signal, each created signal MUST have a unique ID.
	 *
	 * @param string $class Fully qualified class name (including namespace) of desired signal sub type
	 * @return Signal
	 */
	public function create($class = '');

}