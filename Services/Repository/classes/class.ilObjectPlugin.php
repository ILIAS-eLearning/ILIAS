<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2.php");

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
	* Constructor.
	*/
	function __construct($a_ref_id = 0)
	{
		$this->initType();
		parent::__construct($a_ref_id, true);
		$this->plugin = $this->getPlugin();
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
