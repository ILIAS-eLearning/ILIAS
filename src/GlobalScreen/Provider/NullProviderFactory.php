<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class NullProviderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullProviderFactory implements ProviderFactoryInterface {

	/**
	 * @inheritDoc
	 */
	public function getMainBarProvider(): array {
		return [];
	}


	/**
	 * @inheritDoc
	 */
	public function getMainBarItemInformation(): ItemInformation {
		return null;
	}


	/**
	 * @inheritDoc
	 */
	public function getToolProvider(): array {
		return [];
	}


	/**
	 * @inheritDoc
	 */
	public function getMetaBarProvider(): array {
		return [];
	}


	/**
	 * @inheritDoc
	 */
	public function getProviderByClassName(string $class_name): Provider {
		// return new NullP;
	}


	/**
	 * @inheritDoc
	 */
	public function isInstanceCreationPossible(string $class_name): bool {
		return false;
	}
}
