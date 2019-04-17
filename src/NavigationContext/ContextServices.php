<?php namespace ILIAS\NavigationContext;

use ILIAS\GlobalScreen\Scope\View\ViewFactory;
use ILIAS\NavigationContext\Stack\ContextCollection;
use ILIAS\NavigationContext\Stack\ContextStack;

/**
 * Class ContextServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextServices {

	/**
	 * @var ContextRepository
	 */
	private $context_repository;
	/**
	 * @var ContextCollection
	 */
	private $collection;


	/**
	 * ContextServices constructor.
	 *
	 * @param ViewFactory $view_factory
	 */
	public function __construct(ViewFactory $view_factory) {
		$this->context_repository = new ContextRepository($view_factory);
		$this->collection = new ContextCollection($this->context_repository);
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


	/**
	 * @return ContextRepository
	 */
	public function factory(): ContextRepository {
		return $this->context_repository;
	}
}
