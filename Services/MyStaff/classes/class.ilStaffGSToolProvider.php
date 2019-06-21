<?php

use ILIAS\GlobalScreen\Scope\Tool\Context\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Tool\Context\Stack\ContextCollection;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;

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
		return $this->dic->globalScreen()->tool()->context()->collection()->desktop();
	}


	/**
	 * @inheritDoc
	 */
	public function getToolsForContextStack(CalledContexts $called_contexts): array {
		$tools = [];

		return $tools;
	}
}
