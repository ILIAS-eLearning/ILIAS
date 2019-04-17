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
	public function __construct(ContextRepository $context_repository) {
		$this->stack = new ContextStack();
		$this->repo = $context_repository;
	}


	/**
	 * @return ContextCollection
	 */
	public function main(): ContextCollection {
		$context = $this->repo->main();
		$this->stack->push($context);

		return $this;
	}


	/**
	 * @return ContextCollection
	 */
	public function desktop(): ContextCollection {
		$this->stack->push($this->repo->desktop());

		return $this;
	}


	/**
	 * @return ContextCollection
	 */
	public function repository(): ContextCollection {
		$this->stack->push($this->repo->repository());

		return $this;
	}


	/**
	 * @return ContextCollection
	 */
	public function administration(): ContextCollection {
		$this->stack->push($this->repo->administration());

		return $this;
	}


	/**
	 * @return ContextStack
	 */
	public function getStack(): ContextStack {
		return $this->stack;
	}
}
