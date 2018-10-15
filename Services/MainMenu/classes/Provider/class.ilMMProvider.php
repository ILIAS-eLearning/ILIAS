<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\AbstractStaticMainMenuProvider;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;

/**
 * Class ilMMProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMProvider extends AbstractStaticMainMenuProvider implements StaticMainMenuProvider {

	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem[]
	 */
	public function getStaticTopItems(): array {
		return [];
	}


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem[]
	 */
	public function getStaticSubItems(): array {
		return [];
	}
}
