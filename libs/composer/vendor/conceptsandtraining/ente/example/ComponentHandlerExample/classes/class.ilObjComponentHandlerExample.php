<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Ente\ILIAS;

/**
 * Object of the plugin
 */
class ilObjComponentHandlerExample extends ilObjectPlugin {
	use ILIAS\ilHandlerObjectHelper;

	protected function getDIC() {
		return $GLOBALS["DIC"];
	}

	/**
	 * Init the type of the plugin. Same value as choosen in plugin.php
	 */
	public function initType() {
		$this->setType("xleh");
	}

    /**
     * Returns an array with title => string[] entries containing the strings
     * provided for the object this plugin object is contained in.
     *
     * @return  array<string,string[]>
     */
    public function getProvidedStrings() {
		$components = $this->getComponentsOfType(\CaT\Ente\Simple\AttachString::class);

        $provided_strings = [];
		foreach ($components as $component) {
            $provided_strings[] = $component->attachedString();
        }

        return $provided_strings;
    }

    /**
	 * Get the ref_id of the object this object handles components for.
	 *
	 * @return int
     */
    protected function getEntityRefId() {
        global $DIC;
        return $DIC->repositoryTree()->getParentId($this->getRefId());
    }
}
