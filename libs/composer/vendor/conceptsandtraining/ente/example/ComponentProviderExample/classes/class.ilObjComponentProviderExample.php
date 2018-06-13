<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\ComponentProviderExample\UnboundProvider;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjComponentProviderExample extends ilObjectPlugin {
	use ilProviderObjectHelper;

	protected function getDIC() {
		return $GLOBALS["DIC"];
	}

	/**
	 * Init the type of the plugin. Same value as choosen in plugin.php
	 */
	public function initType() {
		$this->setType("xlep");
	}

	/**
	 * Creates ente-provider.
	 */
	public function doCreate() {
		$this->createUnboundProvider("crs", UnboundProvider::class, __DIR__."/UnboundProvider.php");
	}

	/**
	 * Get called if the object should be deleted.
	 * Delete additional settings
	 */
	public function doDelete() {
        $db = $this->plugin->settingsDB();
        $db->deleteFor($this->getId());
        $this->deleteUnboundProviders();
	}

	/**
	 * Get the strings provided by this object.
	 *
	 * @return	string[]
	 */
	public function getProvidedStrings() {
		$settings = $this->plugin->settingsDB()->getFor((int)$this->getId());
		$returns = [];
		foreach($settings->providedStrings() as $s) {
			$returns[] = $s;
		}
		return $returns;
	}
}
