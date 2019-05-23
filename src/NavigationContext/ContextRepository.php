<?php namespace ILIAS\NavigationContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\Layout\Definition\LayoutDefinition;
use ILIAS\GlobalScreen\Scope\Layout\Definition\LayoutDefinitionFactory;

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
	 * @var LayoutDefinitionFactory
	 */
	private $view_factory;


	/**
	 * ContextRepository constructor.
	 *
	 * @param LayoutDefinitionFactory $view_factory
	 */
	public function __construct(LayoutDefinitionFactory $view_factory) {
		$this->view_factory = $view_factory;
	}


	/**
	 * @return ContextInterface
	 */
	public function main(): ContextInterface {
		return $this->get(BasicContext::class, self::C_MAIN, $this->view_factory->standardLayout());
	}


	/**
	 * @return ContextInterface
	 */
	public function internal(): ContextInterface {
		return $this->get(BasicContext::class, 'internal', $this->view_factory->standardLayout());
	}


	/**
	 * @return ContextInterface
	 */
	public function external(): ContextInterface {
		return $this->get(BasicContext::class, 'external', $this->view_factory->publicLayout());
	}


	/**
	 * @return ContextInterface
	 */
	public function desktop(): ContextInterface {
		return $this->get(BasicContext::class, self::C_DESKTOP, $this->view_factory->standardLayout());
	}


	/**
	 * @return ContextInterface
	 */
	public function repository(): ContextInterface {
		$context = $this->get(BasicContext::class, self::C_REPO, $this->view_factory->standardLayout());
		$context = $context->withReferenceId(new ReferenceId((int) $_GET['ref_id']));

		return $context;
	}


	/**
	 * @return ContextInterface
	 */
	public function administration(): ContextInterface {
		return $this->get(BasicContext::class, self::C_ADMINISTRATION, $this->view_factory->standardLayout());
	}


	/**
	 * @param string $class_name
	 * @param string $identifier
	 *
	 * @return ContextInterface
	 */
	private function get(string $class_name, string $identifier, LayoutDefinition $view) {
		if (!isset(self::$contexts[$identifier])) {
			self::$contexts[$identifier] = new $class_name($identifier, $view);
		}

		return self::$contexts[$identifier];
	}
}
