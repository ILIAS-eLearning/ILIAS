<?php namespace ILIAS\NavigationContext;

use ILIAS\NavigationContext\Stack\CalledContexts;
use ILIAS\NavigationContext\Stack\ContextCallService;

/**
 * Class ContextServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextServices {

	/**
	 * @var
	 */
	private $stack;


	/**
	 * ContextServices constructor.
	 */
	public function __construct() {
		$this->stack = new CalledContexts();
	}


	/**
	 * @return CalledContexts
	 */
	public function stack(): CalledContexts {
		return $this->stack;
	}


	/**
	 * @return ContextCallService
	 */
	public function call(): ContextCallService {
		return new ContextCallService($this->stack);
	}


	/**
	 * @return ContextRepository
	 */
	public function availableContexts(): ContextRepository {
		return new ContextRepository();
	}
}
