<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


include_once("./Services/Component/classes/class.ilComponent.php");

/**
* @defgroup ServicesComponent Services/Component
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
abstract class ilPlugin
{
	/**
	* Constructor
	*/
	final function __construct()
	{
		$this->__init();
	}

	/**
	* Get Component Type
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Component Type
	*/
	abstract function getComponentType();

	/**
	* Get Component Name.
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Component Name
	*/
	abstract function getComponentName();

	/**
	* Get Slot Name.
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Slot Name
	*/
	abstract function getSlot();

	/**
	* Get Slot ID.
	*
	* Must be overwritten in plugin class of plugin slot.
	* (and should be made final)
	*
	* @return	string	Slot Id
	*/
	abstract function getSlotId();
	
	/**
	* Get Plugin Name. Must be same as in class name il<Name>Plugin
	* and must correspond to plugins subdirectory name.
	*
	* Must be overwritten in plugin class of plugin
	* (and should be made final)
	*
	* @return	string	Plugin Name
	*/
	abstract function getPluginName();

	/**
	* Set Id.
	*
	* @param	string	$a_id	Id
	*/
	private final function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	final function getId()
	{
		return $this->id;
	}

	/**
	* Set Version of last update.
	*
	* @param	string	$a_lastupdateversion	Version of last update
	*/
	private final function setLastUpdateVersion($a_lastupdateversion)
	{
		$this->lastupdateversion = $a_lastupdateversion;
	}

	/**
	* Get Version of last update.
	*
	* @return	string	Version of last update
	*/
	final function getLastUpdateVersion()
	{
		return $this->lastupdateversion;
	}

	/**
	* Set Current Version (from plugin.php file).
	*
	* @param	string	$a_version	Current Version (from plugin.php file)
	*/
	private final function setVersion($a_version)
	{
		$this->version = $a_version;
	}

	/**
	* Get Current Version (from plugin.php file).
	*
	* @return	string	Current Version (from plugin.php file)
	*/
	final function getVersion()
	{
		return $this->version;
	}

	/**
	* Set Required ILIAS min. release.
	*
	* @param	string	$a_iliasminversion	Required ILIAS min. release
	*/
	private final function setIliasMinVersion($a_iliasminversion)
	{
		$this->iliasminversion = $a_iliasminversion;
	}

	/**
	* Get Required ILIAS min. release.
	*
	* @return	string	Required ILIAS min. release
	*/
	final function getIliasMinVersion()
	{
		return $this->iliasminversion;
	}

	/**
	* Set Required ILIAS max. release.
	*
	* @param	string	$a_iliasmaxversion	Required ILIAS max. release
	*/
	private final function setIliasMaxVersion($a_iliasmaxversion)
	{
		$this->iliasmaxversion = $a_iliasmaxversion;
	}

	/**
	* Get Required ILIAS max. release.
	*
	* @return	string	Required ILIAS max. release
	*/
	final function getIliasMaxVersion()
	{
		return $this->iliasmaxversion;
	}

	/**
	* Set Active.
	*
	* @param	boolean	$a_active	Active
	*/
	private final function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	* Get Active.
	*
	* @return	boolean	Active
	*/
	final function getActive()
	{
		return $this->active;
	}
	
	/**
	* Set Plugin Slot.
	*
	* @param	object	$a_slot	Plugin Slot
	*/
	protected final function setSlotObject($a_slot)
	{
		$this->slot = $a_slot;
	}

	/**
	* Get Plugin Slot.
	*
	* @return	object	Plugin Slot
	*/
	protected final function getSlotObject()
	{
		return $this->slot;
	}
	
	/**
	* Set DB Version.
	*
	* @param	int	$a_dbversion	DB Version
	*/
	final function setDBVersion($a_dbversion)
	{
		$this->dbversion = $a_dbversion;
	}

	/**
	* Get DB Version.
	*
	* @return	int	DB Version
	*/
	final function getDBVersion()
	{
		return $this->dbversion;
	}

	/**
	* Write DB version to database 
	*
	* @param	int	$a_dbversion	DB Version
	*/
	final function writeDBVersion($a_dbversion)
	{
		global $ilDB;
		
		$this->setDBVersion($a_dbversion);
		
		$q = "UPDATE il_plugin SET db_version = ".$ilDB->quote($this->getDBVersion()).
			" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
			" AND component_name = ".$ilDB->quote($this->getComponentName()).
			" AND slot_id = ".$ilDB->quote($this->getSlotId()).
			" AND name = ".$ilDB->quote($this->getPluginName());

		$ilDB->query($q);
	}

	
	/**
	* Get Plugin Directory
	*
	* @return	object	Plugin Slot
	*/
	protected final function getDirectory()
	{
		return $this->getSlotObject()->getPluginsDirectory()."/".$this->getPluginName();
	}

	/**
	* Get Plugin's classes Directory
	*
	* @return	object	classes directory
	*/
	protected final function getClassesDirectory()
	{
		return $this->getDirectory()."/classes";
	}
	
	/**
	* Include (once) a class file
	*/
	public final function includeClass($a_class_file_name)
	{
		include_once($this->getClassesDirectory()."/".$a_class_file_name);
	}

	/**
	* Get Plugin's language Directory
	*
	* @return	object	classes directory
	*/
	protected final function getLanguageDirectory()
	{
		return $this->getDirectory()."/lang";
	}
	
	/**
	* Get array of all language files in the plugin
	*/
	static final function getAvailableLangFiles($a_lang_directory)
	{
		$langs = array();

		if (!@is_dir($a_lang_directory))
		{
			return array();
		}

		$dir = opendir($a_lang_directory);
		while($file = readdir($dir))
		{
			if ($file != "." and
				$file != "..")
			{
				// directories
				if (@is_file($a_lang_directory."/".$file))
				{
					if (substr($file, 0, 6) == "ilias_" &&
						substr($file, strlen($file) - 5) == ".lang")
					{
						$langs[] = array("key" => substr($file, 6, 2), "file" => $file,
							"path" => $a_lang_directory."/".$file);
					}
				}
			}
		}

		return $langs;
	}
	
	/**
	* Get plugin prefix, used for lang vars
	*/
	final function getPrefix()
	{
		return $this->getSlotObject()->getPrefix()."_".$this->getId();
	}

	/**
	* Get DB update script filename (full path)
	*
	* @return	string		DB Update script name
	*/
	static public final function getDBUpdateScriptName($a_ctype, $a_cname, $a_slot_name, $a_pname)
	{
		return "Customizing/global/plugins/".$a_ctype."/".$a_cname."/".
			$a_slot_name."/".$a_pname."/sql/dbupdate.php";
	}

	/**
	* Get db table plugin prefix
	*/
	final function getTablePrefix()
	{
		return "il_".$this->getPrefix();
	}
	
	/**
	* Update all languages
	*/
	final public function updateLanguages()
	{
		global $ilCtrl;
		
		include_once("./Services/Language/classes/class.ilObjLanguage.php");
		
		$langs = $this->getAvailableLangFiles($this->getLanguageDirectory());
		
		$prefix = $this->getPrefix();
		
		foreach($langs as $lang)
		{
			$txt = file($this->getLanguageDirectory()."/".$lang["file"]);
			$lang_array = array();

			// get language data
			if (is_array($txt))
			{
				foreach ($txt as $row)
				{
					if ($row[0] != "#" && strpos($row, "#:#") > 0)
					{
						$a = explode("#:#",trim($row));
						$lang_array[$prefix."_".trim($a[0])] = trim($a[1]);
					}
				}
			}

			ilObjLanguage::replaceLangModule($lang["key"], $prefix,
				$lang_array);
		}
	}
	
	/**
	* Update database
	*/
	function updateDatabase()
	{
		global $ilDB, $lng;
		
		include_once("./Services/Component/classes/class.ilPluginDBUpdate.php");
		$dbupdate = new ilPluginDBUpdate($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId(),
			$this->getPluginName(), $ilDB, true, $this->getTablePrefix());
		
		//$dbupdate->getDBVersionStatus();
		//$dbupdate->getCurrentVersion();
		
		$result = $dbupdate->applyUpdate();

		if ($dbupdate->updateMsg == "no_changes")
		{
			$message = $lng->txt("no_changes").". ".$lng->txt("database_is_uptodate");
		}
		else
		{
			foreach ($dbupdate->updateMsg as $row)
			{
				$message .= $lng->txt($row["msg"]).": ".$row["nr"]."<br/>";
			}
		}
		
		$this->message.= $message;

		return $result;
	}
	
	/**
	* Load language module for plugin
	*/
	public final function loadLanguageModule()
	{
		global $lng;
		
		$lng->loadLanguageModule($this->getPrefix());
	}
	
	/**
	* Get Language Variable (prefix will be prepended automatically)
	*/
	public final function txt($a_var)
	{
		global $lng;
		return $lng->txt($this->getPrefix()."_".$a_var);
	}
	
	/**
	* Get template from plugin
	*/
	public final function getTemplate($a_template, $a_par1 = true, $a_par2 = true)
	{
		$tpl = new ilTemplate($this->getDirectory()."/templates/".$a_template, $a_par1, $a_par2);
		
		return $tpl;
	}

	/**
	* Get css file location
	*/
	public final function getStyleSheetLocation($a_css_file)
	{
		return $this->getDirectory()."/templates/".$a_css_file;
	}

	/**
	* Add template content to placeholder variable
	*/
	public final function addBlockFile($a_tpl, $a_var, $a_block, $a_tplname)
	{
		$a_tpl->addBlockFile($a_var, $a_block,
			$this->getDirectory()."/templates/".$a_tplname);
	}

	/**
	* Get record from il_plugin table
	*/
	static final public function getPluginRecord($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;
		
		// read/set basic data
		$q = "SELECT * FROM il_plugin".
			" WHERE component_type = ".$ilDB->quote($a_ctype).
			" AND component_name = ".$ilDB->quote($a_cname).
			" AND slot_id = ".$ilDB->quote($a_slot_id).
			" AND name = ".$ilDB->quote($a_pname);
		$set = $ilDB->query($q);
		if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $rec;
		}
		else		// no record? create one
		{
			$q = "INSERT INTO il_plugin (component_type, component_name, slot_id, name)".
				" VALUES (".$ilDB->quote($a_ctype).",".
				$ilDB->quote($a_cname).",".
				$ilDB->quote($a_slot_id).",".
				$ilDB->quote($a_pname).")";
			$ilDB->query($q);
			$q = "SELECT * FROM il_plugin".
				" WHERE component_type = ".$ilDB->quote($a_ctype).
				" AND component_name = ".$ilDB->quote($a_cname).
				" AND slot_id = ".$ilDB->quote($a_slot_id).
				" AND name = ".$ilDB->quote($a_pname);
			$set = $ilDB->query($q);
			return $set->fetchRow(DB_FETCHMODE_ASSOC);
		}
	}
	
	/**
	* Default initialization
	*/
	final private function __init()
	{
		global $ilDB, $lng, $ilPluginAdmin;
		
		// read/set basic data
		$rec = ilPlugin::getPluginRecord($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId(), $this->getPluginName());
		$this->setLastUpdateVersion($rec["last_update_version"]);
		$this->setDBVersion($rec["db_version"]);
		$this->setActive($rec["active"]);
		
		// get id
		$this->setId($ilPluginAdmin->getId($this->getComponentType(),
			$this->getComponentName(),
			$this->getSlotId(),
			$this->getPluginName()));
		
		// get version
		$this->setVersion($ilPluginAdmin->getVersion($this->getComponentType(),
			$this->getComponentName(),
			$this->getSlotId(),
			$this->getPluginName()));
			
		// get ilias min version
		$this->setIliasMinVersion($ilPluginAdmin->getIliasMinVersion($this->getComponentType(),
			$this->getComponentName(),
			$this->getSlotId(),
			$this->getPluginName()));

		// get ilias max version
		$this->setIliasMaxVersion($ilPluginAdmin->getIliasMaxVersion($this->getComponentType(),
			$this->getComponentName(),
			$this->getSlotId(),
			$this->getPluginName()));

		// get slot object
		$this->setSlotObject(new ilPluginSlot($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId()));
		
		// load language module
		$lng->loadLanguageModule($this->getPrefix());
			
		// call slot and plugin init methods
		$this->slotInit();
		$this->init();
	}

	/**
	* Object initialization done by slot.
	* Must be overwritten in plugin class of plugin slot.
	*
	* (and should be made protected final)
	*/
	abstract protected function slotInit();

	/**
	* Object initialization. Can be overwritten by plugin class
	* (and should be made protected final)
	*/
	protected function init()
	{
	}

	/**
	* Check whether plugin is active
	*/
	public final function isActive()
	{
		global $ilPluginAdmin;
		
		return $ilPluginAdmin->isActive($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId(), $this->getPluginName());
	}
	
	/**
	* Check whether update is needed.
	*/
	public final function needsUpdate()
	{
		global $ilPluginAdmin;
		
		return $ilPluginAdmin->isActive($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId(), $this->getPluginName());
	}
	
	/**
	* Activate 
	*/
	final function activate()
	{
		global $lng, $ilDB;
		
		$result = true;

		// check whether update is necessary
		if ($this->needsUpdate())
		{
			$result = $this->isUpdatePossible();
			
			// do update
			if ($result === true)
			{
				$result = $this->update();
			}
		}
		
		// activate plugin
		if ($result === true)
		{
			$q = "UPDATE il_plugin SET active = 1".
				" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
				" AND component_name = ".$ilDB->quote($this->getComponentName()).
				" AND slot_id = ".$ilDB->quote($this->getSlotId()).
				" AND name = ".$ilDB->quote($this->getPluginName());
				
			$ilDB->query($q);
		}
	}

	/**
	* Deactivate 
	*/
	final function deactivate()
	{
		global $ilDB;
		
		$result = true;
		
		$q = "UPDATE il_plugin SET active = 0".
			" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
			" AND component_name = ".$ilDB->quote($this->getComponentName()).
			" AND slot_id = ".$ilDB->quote($this->getSlotId()).
			" AND name = ".$ilDB->quote($this->getPluginName());
			
		$ilDB->query($q);

		return $result;
	}
	
	/**
	* Update
	*/
	final function update()
	{
		global $ilDB, $ilCtrl;
		
		$result = true;
		
		// DB update
		if ($result === true)
		{
			$result = $this->updateDatabase();
		}
		
		// Load language files
		$this->updateLanguages();
		
		// load control structure
		chdir("./setup");
		include_once("./classes/class.ilCtrlStructureReader.php");
		$structure_reader = new ilCtrlStructureReader();
		$structure_reader->readStructure(true, "../".$this->getDirectory(), $this->getPrefix());
		chdir("..");
		$ilCtrl->storeCommonStructures();
		
		// set last update version to current version
		if ($result === true)
		{
			$q = "UPDATE il_plugin SET last_update_version = ".$ilDB->quote($this->getVersion()).
				" WHERE component_type = ".$ilDB->quote($this->getComponentType()).
				" AND component_name = ".$ilDB->quote($this->getComponentName()).
				" AND slot_id = ".$ilDB->quote($this->getSlotId()).
				" AND name = ".$ilDB->quote($this->getPluginName());
				
			$ilDB->query($q);
		}

		return $result;
	}
	
	/**
	* Get plugin object.
	*
	* @param	string	$a_ctype	IL_COMP_MODULE | IL_COMP_SERVICE
	* @param	string	$a_cname	component name
	* @param	string	$a_sname	plugin slot name
	* @param	string	$a_pname	plugin name
	*/
	final static function getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;
		
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		$slot_name = ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);
		
		// this check is done due to security reasons
		$st = $ilDB->prepare("SELECT * FROM il_component WHERE type = ? ".
			" AND name = ?", array("text", "text"));
		$set = $ilDB->execute($st, array($a_ctype, $a_cname));			
		if (!$ilDB->fetchAssoc($set))
		{
			return null;
		}
		
		$file = "./Customizing/global/plugins/".$a_ctype."/".
			$a_cname."/".$slot_name."/".
			$a_pname."/classes/class.il".$a_pname."Plugin.php";

		if (is_file($file))
		{
			include_once($file);
			$class = "il".$a_pname."Plugin";
			$plugin = new $class();
			return $plugin;
		}
		
		return null;
	}
	
	
	/**
	* Lookup information data in il_plugin
	*/
	final static function lookupStoredData($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_plugin WHERE ".
				" component_type = ".$ilDB->quote($a_ctype)." AND ".
				" component_name = ".$ilDB->quote($a_cname)." AND ".
				" slot_id = ".$ilDB->quote($a_slot_id)." AND ".
				" name = ".$ilDB->quote($a_pname);

		$set = $ilDB->query($q);
		
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		
		return $rec;
	}
	
	/**
	* Get all active plugins for a slot
	*/
	static final function getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id)
	{
		global $ilDB, $ilPluginAdmin;
		
		$q = "SELECT * FROM il_plugin WHERE component_type = ".$ilDB->quote($a_ctype).
			" AND component_name = ".$ilDB->quote($a_cname).
			" AND slot_id = ".$ilDB->quote($a_slot_id).
			" AND active = 1";

		$set = $ilDB->query($q);
		$plugins = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($ilPluginAdmin->isActive($a_ctype, $a_cname, $a_slot_id, $rec["name"]))
			{
				$plugins[] = $rec["name"];
			}
		}
		
		return $plugins;
	}
}
?>
