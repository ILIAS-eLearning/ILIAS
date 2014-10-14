<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Component/classes/class.ilComponent.php");
include_once("./Services/Component/exceptions/class.ilPluginException.php");

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
	protected $lang_initialised = false;

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

		$q = "UPDATE il_plugin SET db_version = ".$ilDB->quote((int) $this->getDBVersion(), "integer").
			" WHERE component_type = ".$ilDB->quote($this->getComponentType(), "text").
			" AND component_name = ".$ilDB->quote($this->getComponentName(), "text").
			" AND slot_id = ".$ilDB->quote($this->getSlotId(), "text").
			" AND name = ".$ilDB->quote($this->getPluginName(), "text");

		$ilDB->manipulate($q);
	}


	/**
	 * Get Plugin Directory
	 *
	 * @return	object	Plugin Slot
	 */
	public final function getDirectory()
	{
		return $this->getSlotObject()->getPluginsDirectory()."/".$this->getPluginName();
	}

	/**
	 * Get plugin directory
	 */
	static public final function _getDirectory($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		include_once "Services/Component/classes/class.ilPluginSlot.php";
		return ilPluginSlot::_getPluginsDirectory($a_ctype, $a_cname, $a_slot_id)."/".$a_pname;
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
	 * Has the plugin a configure class?
	 *
	 * @param	string	slot directory
	 * @param	string	plugin name
	 * @return	boolean	true/false
	 */
	static final function hasConfigureClass($a_slot_dir, $a_name)
	{
		if (is_file($a_slot_dir."/".
			$a_name."/classes/class.il".$a_name."ConfigGUI.php"))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get plugin configure class name
	 *
	 * @param
	 * @return
	 */
	static final function getConfigureClassName($a_name)
	{
		return "il".$a_name."ConfigGUI";
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
		return $this->getPrefix();
	}

	/**
	 * Update all languages
	 */
	final public function updateLanguages()
	{
		global $ilCtrl;
		ilGlobalCache::flushAll();
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
						ilObjLanguage::replaceLangEntry($prefix, $prefix."_".trim($a[0]),
							$lang["key"], trim($a[1]));
						//echo "<br>-$prefix-".$prefix."_".trim($a[0])."-".$lang["key"]."-";
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

		ilCachedComponentData::flush();

		include_once("./Services/Component/classes/class.ilPluginDBUpdate.php");
		$dbupdate = new ilPluginDBUpdate($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId(),
			$this->getPluginName(), $ilDB, true, $this->getTablePrefix());

		//$dbupdate->getDBVersionStatus();
		//$dbupdate->getCurrentVersion();

		$result = $dbupdate->applyUpdate();
		$message = '';
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

		if (!$this->lang_initialised && is_object($lng))
		{
			$lng->loadLanguageModule($this->getPrefix());
			$this->lang_initialised = true;
		}
	}

	/**
	 * Get Language Variable (prefix will be prepended automatically)
	 */
	public final function txt($a_var)
	{
		global $lng;
		$this->loadLanguageModule();
		return $lng->txt($this->getPrefix()."_".$a_var, $this->getPrefix());
	}

	/**
	 * Lookup language text
	 */
	static function lookupTxt($a_mod_prefix, $a_pl_id, $a_lang_var)
	{
		global $lng;

		// this enables default language fallback
		$prefix = $a_mod_prefix."_".$a_pl_id;
		return $lng->txt($prefix."_".$a_lang_var, $prefix);

		/*
		return ilLanguage::_lookupEntry($lng->lang_key, $a_mod_prefix."_".$a_pl_id,
			$a_mod_prefix."_".$a_pl_id."_".$a_lang_var);		 
		*/
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
	 * Get image path
	 */
	public static final function _getImagePath($a_ctype, $a_cname, $a_slot_id,
		$a_pname, $a_img)
	{
		$d2 = ilComponent::lookupId($a_ctype, $a_cname)."_".$a_slot_id."_".
			ilPlugin::lookupIdForName($a_ctype, $a_cname, $a_slot_id, $a_pname);

		$img = ilUtil::getImagePath($d2."/".$a_img);
		if (is_int(strpos($img, "Customizing")))
		{
			return $img;
		}

		$d = ilPlugin::_getDirectory($a_ctype, $a_cname, $a_slot_id, $a_pname);
		return $d."/templates/images/".$a_img;
	}

	/**
	 * Get image path
	 */
	public final function getImagePath($a_img)
	{
		return self::_getImagePath($this->getComponentType(), $this->getComponentName(), $this->getSlotId(),
			$this->getPluginName(), $a_img);
	}

	/**
	 * Get css file location
	 */
	public final function getStyleSheetLocation($a_css_file)
	{
		$d2 = ilComponent::lookupId($this->getComponentType(), $this->getComponentName())."_".$this->getSlotId()."_".
			ilPlugin::lookupIdForName($this->getComponentType(), $this->getComponentName(), $this->getSlotId(), $this->getPluginName());

		$css = ilUtil::getStyleSheetLocation("output", $a_css_file, $d2);
		if (is_int(strpos($css, "Customizing")))
		{
			return $css;
		}

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
	 * Create plugin record, if not existing
	 */
	static final public function createPluginRecord($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;

		ilCachedComponentData::flush();

		// check record existence record
		$q = "SELECT * FROM il_plugin".
			" WHERE component_type = ".$ilDB->quote($a_ctype, "text").
			" AND component_name = ".$ilDB->quote($a_cname, "text").
			" AND slot_id = ".$ilDB->quote($a_slot_id, "text").
			" AND name = ".$ilDB->quote($a_pname, "text");
		$set = $ilDB->query($q);
		if (!$rec = $ilDB->fetchAssoc($set))
		{
			$q = "INSERT INTO il_plugin (component_type, component_name, slot_id, name)".
				" VALUES (".$ilDB->quote($a_ctype, "text").",".
				$ilDB->quote($a_cname, "text").",".
				$ilDB->quote($a_slot_id, "text").",".
				$ilDB->quote($a_pname, "text").")";
			$ilDB->manipulate($q);
		}
	}


	/**
	 * Get record from il_plugin table
	 */
	static final public function getPluginRecord($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		$cached_component = ilCachedComponentData::getInstance();
		$rec = $cached_component->lookupPluginByName($a_pname);

		if ($rec['component_type'] == $a_ctype AND $rec['component_name'] == $a_cname AND $rec['slot_id'] == $a_slot_id) {
			return $rec;
		} else {
			include_once("./Services/Component/exceptions/class.ilPluginException.php");
			throw (new ilPluginException("No plugin record found for '" . $a_ctype . "', '" . $a_cname . "', '" . $a_slot_id . "', '" . $a_pname
				. "'."));

		}
		//
		//		global $ilDB;
		//
		//		// read/set basic data
		//		$q = "SELECT * FROM il_plugin".
		//			" WHERE component_type = ".$ilDB->quote($a_ctype, "text").
		//			" AND component_name = ".$ilDB->quote($a_cname, "text").
		//			" AND slot_id = ".$ilDB->quote($a_slot_id, "text").
		//			" AND name = ".$ilDB->quote($a_pname, "text");
		//		$set = $ilDB->query($q);
		//		if ($rec = $ilDB->fetchAssoc($set))
		//		{
		//			return $rec;
		//		}
		//		else		// no record? create one
		//		{
		//			// silently create these records is not a good idea, since
		//			// the function can be called with "wrong parameters"
		//			// raise exceptions instead
		//			include_once("./Services/Component/exceptions/class.ilPluginException.php");
		//			throw (new ilPluginException("No plugin record found for '".$a_ctype."', '".$a_cname."', '".$a_slot_id."', '".$a_pname."'."));
		//
		//			$q = "INSERT INTO il_plugin (component_type, component_name, slot_id, name)".
		//				" VALUES (".$ilDB->quote($a_ctype, "text").",".
		//				$ilDB->quote($a_cname, "text").",".
		//				$ilDB->quote($a_slot_id, "text").",".
		//				$ilDB->quote($a_pname, "text").")";
		//			$ilDB->manipulate($q);
		//			$q = "SELECT * FROM il_plugin".
		//				" WHERE component_type = ".$ilDB->quote($a_ctype, "text").
		//				" AND component_name = ".$ilDB->quote($a_cname, "text").
		//				" AND slot_id = ".$ilDB->quote($a_slot_id, "text").
		//				" AND name = ".$ilDB->quote($a_pname, "text");
		//			$set = $ilDB->query($q);
		//			return $ilDB->fetchAssoc($set);
		//		}
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

		// Fix for authentication plugins
		$this->loadLanguageModule();

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

		ilCachedComponentData::flush();

		$result = true;

		// check whether update is necessary
		if ($this->needsUpdate())
		{
			//$result = $this->isUpdatePossible();

			// do update
			if ($result === true)
			{
				$result = $this->update();
			}
		}
		if ($result === true)
		{
			$result = $this->beforeActivation();
			// activate plugin
			if ($result === true)
			{
				$q = "UPDATE il_plugin SET active = ".$ilDB->quote(1, "integer").",".
					" plugin_id = ".$ilDB->quote($this->getId(), "text").
					" WHERE component_type = ".$ilDB->quote($this->getComponentType(), "text").
					" AND component_name = ".$ilDB->quote($this->getComponentName(), "text").
					" AND slot_id = ".$ilDB->quote($this->getSlotId(), "text").
					" AND name = ".$ilDB->quote($this->getPluginName(), "text");

				$ilDB->manipulate($q);
				$this->afterActivation();
			}
		}
		ilCachedComponentData::flush();
		return $result;
	}

	/**
	 * Before activation processing
	 */
	protected function beforeActivation()
	{
		return true;	// false would indicate that anything went wrong
		// activation would not proceed
		// throw an exception in this case
		//throw new ilPluginException($lng->txt(""));
	}

	/**
	 * After activation processing
	 */
	protected function afterActivation()
	{
	}

	/**
	 * Deactivate
	 */
	final function deactivate()
	{
		global $ilDB;

		ilCachedComponentData::flush();

		$result = true;

		$q = "UPDATE il_plugin SET active = ".$ilDB->quote(0, "integer").
			" WHERE component_type = ".$ilDB->quote($this->getComponentType(), "text").
			" AND component_name = ".$ilDB->quote($this->getComponentName(), "text").
			" AND slot_id = ".$ilDB->quote($this->getSlotId(), "text").
			" AND name = ".$ilDB->quote($this->getPluginName(), "text");

		$ilDB->manipulate($q);

		return $result;
	}


	/**
	 * After deactivation processing
	 */
	protected function afterDeactivation()
	{
	}

	/**
	 * Update plugin
	 */
	final function update()
	{
		global $ilDB, $ilCtrl;

		ilCachedComponentData::flush();

		$result = $this->beforeUpdate();
		if ($result === false) {
			return false;
		}

		// DB update
		if ($result === true)
		{
			$result = $this->updateDatabase();
		}

		// Load language files
		$this->updateLanguages();

		// load control structure
		include_once("./setup/classes/class.ilCtrlStructureReader.php");
		$structure_reader = new ilCtrlStructureReader();
		$structure_reader->readStructure(true, "./".$this->getDirectory(), $this->getPrefix(),
			$this->getDirectory());
		//		$ilCtrl->storeCommonStructures();

		// add config gui to the ctrl calls
		$ilCtrl->insertCtrlCalls("ilobjcomponentsettingsgui", ilPlugin::getConfigureClassName($this->getPluginName()),
			$this->getPrefix());

		// set last update version to current version
		if ($result === true)
		{
			$q = "UPDATE il_plugin SET last_update_version = ".$ilDB->quote($this->getVersion(), "text").
				" WHERE component_type = ".$ilDB->quote($this->getComponentType(), "text").
				" AND component_name = ".$ilDB->quote($this->getComponentName(), "text").
				" AND slot_id = ".$ilDB->quote($this->getSlotId(), "text").
				" AND name = ".$ilDB->quote($this->getPluginName(), "text");

			$ilDB->manipulate($q);
			$this->afterUpdate();
		}

		return $result;
	}

	/**
	 * Before update processing
	 */
	protected function beforeUpdate()
	{
		return true;	// false would indicate that anything went wrong
		// update would not proceed
		// throw an exception in this case
		//throw new ilPluginException($lng->txt(""));
	}

	/**
	 * After update processing
	 */
	protected function afterUpdate()
	{
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

		$cached_component = ilCachedComponentData::getInstance();
		$rec = $cached_component->lookCompId($a_ctype, $a_cname);
		if (! $rec) {
			return NULL;
		}

		// this check is done due to security reasons
		//		$set = $ilDB->queryF("SELECT * FROM il_component WHERE type = %s ".
		//			" AND name = %s", array("text", "text"),
		//			array($a_ctype, $a_cname));
		//		if (!$ilDB->fetchAssoc($set))
		//		{
		//			return null;
		//		}

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
			" component_type = ".$ilDB->quote($a_ctype, "text")." AND ".
			" component_name = ".$ilDB->quote($a_cname, "text")." AND ".
			" slot_id = ".$ilDB->quote($a_slot_id, "text")." AND ".
			" name = ".$ilDB->quote($a_pname, "text");

		$set = $ilDB->query($q);

		$rec = $ilDB->fetchAssoc($set);

		return $rec;
	}

	/**
	 * Get all active plugins for a slot
	 */
	static final function getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id)
	{
		global $ilDB, $ilPluginAdmin;

		$plugins = array();

		//		$q = "SELECT * FROM il_plugin WHERE component_type = ".$ilDB->quote($a_ctype, "text").
		//			" AND component_name = ".$ilDB->quote($a_cname, "text").
		//			" AND slot_id = ".$ilDB->quote($a_slot_id, "text").
		//			" AND active = ".$ilDB->quote(1, "integer");
		//
		//		$set = $ilDB->query($q);
		$cached_component = ilCachedComponentData::getInstance();
		//		while($rec = $ilDB->fetchAssoc($set))
		$lookupActivePluginsBySlotId = $cached_component->lookupActivePluginsBySlotId($a_slot_id);
		foreach($lookupActivePluginsBySlotId as $rec)
		{
			if ($ilPluginAdmin->isActive($a_ctype, $a_cname, $a_slot_id, $rec["name"]))
			{
				$plugins[] = $rec["name"];
			}
		}

		return $plugins;
	}

	/**
	 * Lookup name for id
	 */
	function lookupNameForId($a_ctype, $a_cname, $a_slot_id, $a_plugin_id)
	{
		global $ilDB;

		$q = "SELECT name FROM il_plugin ".
			" WHERE component_type = ".$ilDB->quote($a_ctype, "text").
			" AND component_name = ".$ilDB->quote($a_cname, "text").
			" AND slot_id = ".$ilDB->quote($a_slot_id, "text").
			" AND plugin_id = ".$ilDB->quote($a_plugin_id, "text");

		$set = $ilDB->query($q);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["name"];
		}
	}

	/**
	 * Lookup id for name
	 */
	function lookupIdForName($a_ctype, $a_cname, $a_slot_id, $a_plugin_name)
	{
		global $ilDB;

		$q = "SELECT plugin_id FROM il_plugin ".
			" WHERE component_type = ".$ilDB->quote($a_ctype, "text").
			" AND component_name = ".$ilDB->quote($a_cname, "text").
			" AND slot_id = ".$ilDB->quote($a_slot_id, "text").
			" AND name = ".$ilDB->quote($a_plugin_name, "text");

		$set = $ilDB->query($q);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["plugin_id"];
		}
	}
}
?>