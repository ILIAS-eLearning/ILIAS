<?php namespace ILIAS\GlobalScreen\Scope\Context;

use ILIAS\GlobalScreen\Scope\Context\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Context\Stack\ContextCallService;

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
	public function repository(): ContextRepository {
		return new ContextRepository();
	}
}
