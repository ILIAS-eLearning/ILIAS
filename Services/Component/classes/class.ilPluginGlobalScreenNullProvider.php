<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;

/**
 * Class ilPluginGlobalScreenNullProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class ilPluginGlobalScreenNullProvider extends AbstractStaticPluginMainMenuProvider {

	/**
	 * @inheritDoc
	 */
	public function __construct() {

	}


	/**
	 * @inheritDoc
	 */
	public final function getPurpose(): string {
		return "mainmenu";
	}


	/**
	 * @inheritDoc
	 */
	public final function getStaticTopItems(): array {
		return array();
	}


	/**
	 * @inheritDoc
	 */
	public final function getStaticSubItems(): array {
		return array();
	}
}
