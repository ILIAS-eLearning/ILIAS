<?php

use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\NavigationContext\ContextInterface;

/**
 * Class DynamicContextProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractDynamicContextProvider extends AbstractProvider implements DynamicContextProviderInterface {

	/**
	 * @inheritdoc
	 */
	public function getGeneralContextsForComponent(): array {
		return [];
	}


	/**
	 * @inheritdoc
	 */
	abstract public function enrichContextWithCurrentSituation(ContextInterface $context): ContextInterface;
}
