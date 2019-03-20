<?php namespace ILIAS\NavigationContext\Stack;

use ILIAS\NavigationContext\BasicContext;
use ILIAS\NavigationContext\ContextInterface;
use ILIAS\NavigationContext\ContextRepository;

/**
 * Class ContextCollection
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextCollection {

	const C_MAIN = 'main';
	const C_DESKTOP = 'desktop';
	const C_REPO = 'repo';
	const C_ADMINISTRATION = 'administration';
	const C_MAIL = 'mail';
	/**
	 * @var ContextRepository
	 */
	protected $repo;
	/**
	 * @var ContextStack
	 */
	protected $stack;


	/**
	 * ContextCollection constructor.
	 */
	public function __construct() {
		$this->stack = new ContextStack();
		$this->repo = new ContextRepository();
	}


	/**
	 * @return ContextCollection
	 */
	public function main(): ContextCollection {
		$this->stack->push(new BasicContext(self::C_MAIN));

		return $this;
	}


	/**
	 * @return ContextCollection
	 */
	public function desktop(): ContextCollection {
		$this->stack->push(new BasicContext(self::C_DESKTOP));

		return $this;
	}


	/**
	 * @return ContextCollection
	 */
	public function repository(): ContextCollection {
		$this->stack->push(new BasicContext(self::C_REPO));

		return $this;
	}


	/**
	 * @return ContextCollection
	 */
	public function mail(): ContextCollection {
		$this->stack->push(new BasicContext(self::C_MAIL));

		return $this;
	}


	/**
	 * @return ContextCollection
	 */
	public function administration(): ContextCollection {
		$this->stack->push(new BasicContext(self::C_ADMINISTRATION));

		return $this;
	}


	/**
	 * @return ContextStack
	 */
	public function getStack(): ContextStack {
		return $this->stack;
	}
}
