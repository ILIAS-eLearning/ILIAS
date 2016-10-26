<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2.php");
require_once('./Services/Component/classes/class.ilPlugin.php');
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/*
* Object class for plugins. This one wraps around ilObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
abstract class ilObjectPlugin extends ilObject2
{
	private $object;

	protected $plugin;

	/**
	 * @var
	 */
	protected static $plugin_by_type = array();


	/**
	* Constructor.
	*/
	function __construct($a_ref_id = 0)
	{
		$this->initType();
		parent::__construct($a_ref_id, true);
		$this->plugin = $this->getPlugin();
	}


	/**
	 * Return either a repoObject plugin or a orgunit extension plugin or null if the type is not a plugin.
	 *
	 * @param $type
	 * @return null|ilRepositoryObjectPlugin
	 */
	public static function getRepoPluginObjectByType($type) {
		if (!self::$plugin_by_type[$type]) {
			self::loadRepoPlugin($type);
		}

		return self::$plugin_by_type[$type];
	}


	/**
	 * @param string $type_id
	 */
	protected static function loadRepoPlugin($type_id) {
		$plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj",
			ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $type_id));
		if (!$plugin) {
			$plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext",
				ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $type_id));
		}
		if (!$plugin) {
			ilLoggerFactory::getLogger("obj")->log("Try to get repo plugin obj by type: $type_id. No such type exists for Repository and Org Unit pluginss.");
		}
		self::$plugin_by_type[$type_id] = $plugin;
	}

	/**
	 * @param $plugin_id
	 * @param $lang_var
	 * @return string
	 */
	static function lookupTxtById($plugin_id, $lang_var) {
		$pl = self::getRepoPluginObjectByType($plugin_id);
		return $pl->txt($lang_var);
	}

	/**
	 * Get plugin object
	 * @return object plugin object
	 * @throws ilPluginException
	 */
	protected function getPlugin()
	{
		if(!$this->plugin) {
			$this->plugin =
				ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj",
					ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $this->getType()));
			if (!is_object($this->plugin)) {
				throw new ilPluginException("ilObjectPlugin: Could not instantiate plugin object for type " . $this->getType() . ".");
			}
		}
		return $this->plugin;
	}
	
	/**
	* Wrapper for txt function
	*/
	final protected function txt($a_var)
	{
		return $this->getPlugin()->txt($a_var);
	}

	/**
	 * returns a list of all repository object types which can be a parent of this type.
	 * @return string[]
	 */
	public function getParentTypes() {
		return $this->plugin->getParentTypes();
	}
}
