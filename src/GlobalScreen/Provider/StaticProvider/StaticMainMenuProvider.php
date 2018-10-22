<?php namespace ILIAS\GlobalScreen\Provider\StaticProvider;

use ILIAS\GlobalScreen\Collector\MainMenu\TypeHandler;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;
use ILIAS\GlobalScreen\Provider\StaticProvider;

/**
 * Interface StaticMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StaticMainMenuProvider extends StaticProvider {

	/**
	 * @return TopParentItem[] These are Slates which will be
	 * available for configuration and will be collected once during a
	 * StructureReload.
	 */
	public function getStaticTopItems(): array;


	/**
	 * @return isItem[] These are Entries which will be available for
	 * configuration and will be collected once during a StructureReload
	 */
	public function getStaticSubItems(): array;


	/**
	 * @return TypeHandler[]
	 */
	public function provideTypeHandlers(): array;
}
