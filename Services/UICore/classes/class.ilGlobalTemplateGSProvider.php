<?php

use ILIAS\NavigationContext\ContextInterface;

/**
 * Class ilGlobalTemplateGSProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGlobalTemplateGSProvider extends AbstractDynamicContextProvider {

	/**
	 * @inheritDoc
	 */
	public function getGeneralContextsForComponent(): array {
		return [];
	}


	/**
	 * @inheritDoc
	 */
	public function enrichContextWithCurrentSituation(ContextInterface $context): ContextInterface {
		return $context;
	}
}
