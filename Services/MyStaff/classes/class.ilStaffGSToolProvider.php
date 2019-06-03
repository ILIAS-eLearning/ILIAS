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
		$last = $called_contexts->getLast();
		$additional_data = $last->getAdditionalData();
		$iff = function ($id) { return $this->globalScreen()->identification()->fromSerializedIdentification($id); };
		$l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
		$factory = $this->globalScreen()->tool();

		if ($additional_data->exists('mine')) {
			$tools[] = $factory->tool($iff("providerXY|lorem_tool"))
				->withTitle("A Tool")
				->withContent($l("LOREM {$called_contexts->current()->getReferenceId()->toInt()}"));
			$tools[] = $factory->tool($iff("providerXY|ipsum_tool"))
				->withTitle("A Second Tool")
				->withContent($l("IPSUM"));
		}

		$content = "<pre><strong>This is just a POC!</strong><br>";
		$content .= "You are in the following Contexts:<br>";
		foreach ($called_contexts->getStackAsArray() as $ct) {
			$content .= " - $ct<br>";
		}
		$content .= "<p>You see that Tool since the " . self::class . " is interested in the Context 'repo'. Move to you MyStaff-Page to see more Tools.</p>";

		$content .= "</pre>";

		$tools[] = $factory->tool($iff("providerXY|stack_tool"))
			->withTitle("POC")
			->withContent($l($content));

		return $tools;
	}
}
