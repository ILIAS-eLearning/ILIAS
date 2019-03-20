<?php namespace ILIAS\NavigationContext;

use ILIAS\NavigationContext\Stack\ContextCallService;
use ILIAS\NavigationContext\Stack\ContextCollection;
use ILIAS\NavigationContext\Stack\ContextStack;

/**
 * Class ContextServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextServices {

	/**
	 * @var ContextCollection
	 */
	private $collection;


	/**
	 * ContextServices constructor.
	 */
	public function __construct() {
		$this->collection = new ContextCollection();
	}


	/**
	 * @return ContextStack
	 */
	public function stack(): ContextStack {
		return $this->collection->getStack();
	}


	/**
	 * @return ContextCollection
	 */
	public function claim(): ContextCollection {
		return $this->collection;
	}
}
