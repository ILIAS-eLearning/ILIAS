<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\NavigationContext\Stack\CalledContexts;
use ILIAS\NavigationContext\Stack\ContextCollection;

/**
 * Class ilStaffGSToolProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMediaPoolGSToolProvider extends AbstractDynamicToolProvider {

	/**
	 * @inheritDoc
	 */
	public function isInterestedInContexts(): ContextCollection {
		return $this->dic->navigationContext()->collection()->main();
	}


	/**
	 * @inheritDoc
	 */
	public function getToolsForContextStack(CalledContexts $called_contexts): array {
		$ctrl = $this->dic->ctrl();

		$tools = [];
		$last = $called_contexts->getLast();
		$additional_data = $last->getAdditionalData();
		$iff = function ($id) { return $this->globalScreen()->identification()->fromSerializedIdentification($id); };
		$l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
		$factory = $this->globalScreen()->tool();

		if ($additional_data->exists('mine')) {
			/*$tools[] = $factory->tool($iff("providerXY|lorem_tool"))
				->withTitle("A Tool")
				->withContent($l("LOREM {$called_contexts->current()->getReferenceId()->toInt()}"));
			$tools[] = $factory->tool($iff("providerXY|ipsum_tool"))
				->withTitle("A Second Tool")
				->withContent($l("IPSUM"));*/
		}

		/*
		foreach ($called_contexts->getStackAsArray() as $ct) {
			$content .= " - $ct<br>";
		}*/


		if (strtolower($_GET["baseClass"]) == "ilmediapoolpresentationgui")
		{
			$tools[] = $factory->tool($iff("MediaPool|Tree"))
				->withTitle("Folders")
				->withContent($l($this->getTree()));
		}

		return $tools;
	}

	function getTree()
	{

		global $DIC;

		$pool = ilObjectFactory::getInstanceByRefId((int) $_GET["ref_id"]);
		$pool_gui = new ilObjMediaPoolGUI((int) $_GET["ref_id"]);
		$exp = new ilMediaPoolExplorerGUI($pool_gui, "listMedia", $pool);

		return $exp->getHTML(true);
	}
}
