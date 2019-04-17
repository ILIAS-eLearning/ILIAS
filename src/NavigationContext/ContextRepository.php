<?php namespace ILIAS\NavigationContext;

use ILIAS\GlobalScreen\Scope\View\View;
use ILIAS\GlobalScreen\Scope\View\ViewFactory;

/**
 * Class ContextRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextRepository {

	/**
	 * @var array
	 */
	private static $contexts = [];
	const C_MAIN = 'main';
	const C_DESKTOP = 'desktop';
	const C_REPO = 'repo';
	const C_ADMINISTRATION = 'administration';
	/**
	 * @var ViewFactory
	 */
	private $view_factory;


	/**
	 * ContextRepository constructor.
	 *
	 * @param ViewFactory $view_factory
	 */
	public function __construct(ViewFactory $view_factory) {
		$this->view_factory = $view_factory;
	}


	/**
	 * @return ContextInterface
	 */
	public function main(): ContextInterface {
		return $this->get(BasicContext::class, self::C_MAIN, $this->view_factory->standardView());
	}


	/**
	 * @return ContextInterface
	 */
	public function desktop(): ContextInterface {
		return $this->get(BasicContext::class, self::C_DESKTOP, $this->view_factory->standardView());
	}


	/**
	 * @return ContextInterface
	 */
	public function repository(): ContextInterface {
		return $this->get(BasicContext::class, self::C_REPO, $this->view_factory->standardView());
	}


	/**
	 * @return ContextInterface
	 */
	public function administration(): ContextInterface {
		return $this->get(BasicContext::class, self::C_ADMINISTRATION, $this->view_factory->standardView());
	}


	/**
	 * @param string $class_name
	 * @param string $identifier
	 *
	 * @return ContextInterface
	 */
	private function get(string $class_name, string $identifier, View $view) {
		if (!isset(self::$contexts[$identifier])) {
			self::$contexts[$identifier] = new $class_name($identifier, $view);
		}

		return self::$contexts[$identifier];
	}
}
