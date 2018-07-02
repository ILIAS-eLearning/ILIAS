<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once(__DIR__."/../vendor/autoload.php");

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilComponentProviderExamplePlugin extends ilRepositoryObjectPlugin {
    /**
     * @var \ilDBInterface
     */
    protected $ilDB;

    /**
     * @var \CaT\Plugins\ComponentProviderExamplePlugin\Settings\DB|null
     */
    protected $settings_db = null;

    /**
     * Object initialisation. Overwritten from ilPlugin.
     */
    protected function init() {
        global $DIC;
        $this->ilDB = $DIC->database();
    }

	/**
	 * Get the name of the Plugin
	 *
	 * @return string
	 */
	function getPluginName() {
		return "ComponentProviderExample";
	}

	/**
	 * Defines custom uninstall action like delete table or something else
	 */
	protected function uninstallCustom() {
	}

    /**
     * Get the database for settings.
     *
     * @return \CaT\Plugins\ComponentProviderExample\Settings\DB
     */
    public function settingsDB() {
        if ($this->settings_db === null) {
            $this->settings_db = new \CaT\Plugins\ComponentProviderExample\Settings\ilDB($this->ilDB);
        }
        return $this->settings_db;
    }
}
