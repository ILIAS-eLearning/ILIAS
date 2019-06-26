<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\NavigationContext\Stack\CalledContexts;
use ILIAS\NavigationContext\Stack\ContextCollection;

/**
 * Class ilStaffGSToolProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilStaffGSToolProvider extends AbstractDynamicToolProvider {

	/**
	 * @inheritDoc
	 */
	public function isInterestedInContexts(): ContextCollection {
		return $this->dic->navigationContext()->collection()->desktop();
	}


	/**
	 * @inheritDoc
	 */
	public function getToolsForContextStack(CalledContexts $called_contexts): array {
		$tools = [];

		return $tools;
	}
}
