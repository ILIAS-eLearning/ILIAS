<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Content\LayoutContent;
use ILIAS\GlobalScreen\Scope\Layout\Definition\LayoutDefinitionFactory;
use ILIAS\NavigationContext\ContextInterface;
use ILIAS\UI\Component\Layout\Page\Factory;

/**
 * Class LayoutServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices {

	/**
	 * @var array
	 */
	private static $services = [];


	/**
	 * @return LayoutDefinitionFactory
	 */
	public function definition(): LayoutDefinitionFactory {
		return $this->get(LayoutDefinitionFactory::class);
	}


	/**
	 * @return LayoutContent
	 */
	public function content(): LayoutContent {
		return $this->get(LayoutContent::class);
	}


	/**
	 * @param string $class_name
	 *
	 * @return mixed
	 */
	private function get(string $class_name) {
		if (!isset(self::$services[$class_name])) {
			self::$services[$class_name] = new $class_name();
		}

		return self::$services[$class_name];
	}
}
