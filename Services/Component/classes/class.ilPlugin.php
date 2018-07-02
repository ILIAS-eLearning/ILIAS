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

	/**
	 * @var bool
	 */
	protected $lang_initialised = false;
	/**
	 * @var string
	 */
	protected $id = '';

	public function __construct()
	{
		$this->__init();
	}

	/**
	 * Get Component Type
	 *
	 * Must be overwritten in plugin class of plugin slot.
	 *
	 * @return	string	Component Type
	 */
	abstract function getComponentType();

	/**
	 * Get Component Name.
	 *
	 * Must be overwritten in plugin class of plugin slot.
	 *
	 * @return	string	Component Name
	 */
	abstract function getComponentName();
	/**
	 * Get Slot Name.
	 *
	 * Must be overwritten in plugin class of plugin slot.
	 *
	 * @return	string	Slot Name
	 */
	abstract function getSlot();

	/**
	 * Get Slot ID.
	 *
	 * Must be overwritten in plugin class of plugin slot.
	 *
	 * @return	string	Slot Id
	 */
	abstract function getSlotId();

	/**
	 * Get Plugin Name. Must be same as in class name il<Name>Plugin
	 * and must correspond to plugins subdirectory name.
	 *
	 * Must be overwritten in plugin class of plugin
	 *
	 * @return	string	Plugin Name
	 */
	abstract function getPluginName();

	/**
	 * Set Id.
	 *
	 * @param	string	$a_id	Id
	 */
	private function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get Id.
	 *
	 * @return	string	Id
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set Version of last update.
	 *
	 * @param	string	$a_lastupdateversion	Version of last update
	 */
	private function setLastUpdateVersion($a_lastupdateversion)
	{
		$this->lastupdateversion = $a_lastupdateversion;
	}

	/**
	 * Get Version of last update.
	 *
	 * @return	string	Version of last update
	 */
	function getLastUpdateVersion()
	{
		return $this->lastupdateversion;
	}

	/**
	 * Set Current Version (from plugin.php file).
	 *
	 * @param	string	$a_version	Current Version (from plugin.php file)
	 */
	private function setVersion($a_version)
	{
		$this->version = $a_version;
	}

	/**
	 * Get Current Version (from plugin.php file).
	 *
	 * @return	string	Current Version (from plugin.php file)
	 */
	function getVersion()
	{
		return $this->version;
	}

	/**
	 * Set Required ILIAS min. release.
	 *
	 * @param	string	$a_iliasminversion	Required ILIAS min. release
	 */
	private function setIliasMinVersion($a_iliasminversion)
	{
		$this->iliasminversion = $a_iliasminversion;
	}

	/**
	 * Get Required ILIAS min. release.
	 *
	 * @return	string	Required ILIAS min. release
	 */
	function getIliasMinVersion()
	{
		return $this->iliasminversion;
	}

	/**
	 * Set Required ILIAS max. release.
	 *
	 * @param	string	$a_iliasmaxversion	Required ILIAS max. release
	 */
	private function setIliasMaxVersion($a_iliasmaxversion)
	{
		$this->iliasmaxversion = $a_iliasmaxversion;
	}

	/**
	 * Get Required ILIAS max. release.
	 *
	 * @return	string	Required ILIAS max. release
	 */
	function getIliasMaxVersion()
	{
		return $this->iliasmaxversion;
	}

	/**
	 * Set Active.
	 *
	 * @param	boolean	$a_active	Active
	 */
	private function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	 * Get Active.
	 *
	 * @return	boolean	Active
	 */
	function getActive()
	{
		return $this->active;
	}

	/**
	 * Set Plugin Slot.
	 *
	 * @param	object	$a_slot	Plugin Slot
	 */
	protected function setSlotObject($a_slot)
	{
		$this->slot = $a_slot;
	}

	/**
	 * Get Plugin Slot.
	 *
	 * @return	object	Plugin Slot
	 */
	protected function getSlotObject()
	{
		return $this->slot;
	}

	/**
	 * Set DB Version.
	 *
	 * @param	int	$a_dbversion	DB Version
	 */
	function setDBVersion($a_dbversion)
	{
		$this->dbversion = $a_dbversion;
	}

	/**
	 * Get DB Version.
	 *
	 * @return	int	DB Version
	 */
	function getDBVersion()
	{
		return $this->dbversion;
	}

	/**
	 * Write DB version to database
	 *
	 * @param	int	$a_dbversion	DB Version
	 */
	function writeDBVersion($a_dbversion)
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
	public function getDirectory()
	{
		return $this->getSlotObject()->getPluginsDirectory()."/".$this->getPluginName();
	}

	/**
	 * Get plugin directory
	 */
	static public function _getDirectory($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		include_once "Services/Component/classes/class.ilPluginSlot.php";
		return ilPluginSlot::_getPluginsDirectory($a_ctype, $a_cname, $a_slot_id)."/".$a_pname;
	}


	/**
	 * Get Plugin's classes Directory
	 *
	 * @return	object	classes directory
	 */
	protected function getClassesDirectory()
	{
		return $this->getDirectory()."/classes";
	}

	/**
	 * Include (once) a class file
	 */
	public function includeClass($a_class_file_name)
	{
		include_once($this->getClassesDirectory()."/".$a_class_file_name);
	}

	/**
	 * Get Plugin's language Directory
	 *
	 * @return	object	classes directory
	 */
	protected function getLanguageDirectory()
	{
		return $this->getDirectory()."/lang";
	}

	/**
	 * Get array of all language files in the plugin
	 */
	static function getAvailableLangFiles($a_lang_directory)
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
	static function hasConfigureClass($a_slot_dir, $a_name)
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
	static function getConfigureClassName($a_name)
	{
		return "il".$a_name."ConfigGUI";
	}

	/**
	 * Get plugin prefix, used for lang vars
	 */
	function getPrefix()
	{
		return $this->getSlotObject()->getPrefix()."_".$this->getId();
	}

	/**
	 * Get DB update script filename (full path)
	 *
	 * @return	string		DB Update script name
	 */
	static public function getDBUpdateScriptName($a_ctype, $a_cname, $a_slot_name, $a_pname)
	{
		return "Customizing/global/plugins/".$a_ctype."/".$a_cname."/".
		$a_slot_name."/".$a_pname."/sql/dbupdate.php";
	}

	/**
	 * Get db table plugin prefix
	 */
	function getTablePrefix()
	{
		return $this->getPrefix();
	}

	/**
	 * Update all or selected languages
	 * @var array|null	$a_lang_keys	keys of languages to be updated (null for all)
	 */
	public function updateLanguages($a_lang_keys = null)
	{
		ilGlobalCache::flushAll();
		include_once("./Services/Language/classes/class.ilObjLanguage.php");

		// get the keys of all installed languages if keys are not provided
		if(!isset($a_lang_keys))
		{
			$a_lang_keys = array();
			foreach (ilObjLanguage::getInstalledLanguages() as $langObj)
			{
				if ($langObj->isInstalled())
				{
					$a_lang_keys[] = $langObj->getKey();
				}
			}
		}

		$langs = $this->getAvailableLangFiles($this->getLanguageDirectory());

		$prefix = $this->getPrefix();

		foreach($langs as $lang)
		{
			// check if the language should be updated, otherwise skip it
			if (!in_array($lang['key'], $a_lang_keys) )
			{
				continue;
			}

			$txt = file($this->getLanguageDirectory()."/".$lang["file"]);
			$lang_array = array();

			// get locally changed variables of the module (these should be kept)
			$local_changes = ilObjLanguage::_getLocalChangesByModule($lang['key'], $prefix);

			// get language data
			if (is_array($txt))
			{
				foreach ($txt as $row)
				{
					if ($row[0] != "#" && strpos($row, "#:#") > 0)
					{
						$a = explode("#:#",trim($row));
						$identifier = $prefix."_".trim($a[0]);
						$value = trim($a[1]);

						if (isset($local_changes[$identifier]))
						{
							$lang_array[$identifier] = $local_changes[$identifier];
						}
						else
						{
							$lang_array[$identifier] = $value;
							ilObjLanguage::replaceLangEntry($prefix, $identifier, $lang["key"], $value);
						}
						//echo "<br>-$prefix-".$prefix."_".trim($a[0])."-".$lang["key"]."-";
					}
				}
			}

			ilObjLanguage::replaceLangModule($lang["key"], $prefix, $lang_array);
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
	public function loadLanguageModule()
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
	public function txt($a_var)
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
	}

	// cat-tms-patch start
	/**
	 * Lookup language text by id
	 *
	 * @param string 	$plugin_id
	 * @param string 	$a_lang_var
	 *
	 * @return string
	 */
	static function lookupTxtById($plugin_id, $a_lang_var)
	{
		global $lng;
		$pl = ilPluginAdmin::getPluginObjectById($plugin_id);
		$pl->loadLanguageModule();
		return $lng->txt($pl->getPrefix()."_".$a_lang_var, $pl->getPrefix());
	}
	// cat-tms-patch end 

	/**
	 * Is searched lang var available in plugin lang files
	 * 
	 * @param int 		$plugin_id
	 * @param string 	$langVar
	 *
	 * @return bool
	 */
	static function langExitsById($pluginId, $langVar) {
		global $lng;

		$pl = ilObjectPlugin::getRepoPluginObjectByType($pluginId);
		$pl->loadLanguageModule();

		return $lng->exists($pl->getPrefix()."_".$langVar);
	}


	/**
	 * Get template from plugin
	 */
	public function getTemplate($a_template, $a_par1 = true, $a_par2 = true)
	{
		$tpl = new ilTemplate($this->getDirectory()."/templates/".$a_template, $a_par1, $a_par2);

		return $tpl;
	}

	/**
	 * Get image path
	 */
	public static function _getImagePath($a_ctype, $a_cname, $a_slot_id,
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
	public function getImagePath($a_img)
	{
		return self::_getImagePath($this->getComponentType(), $this->getComponentName(), $this->getSlotId(),
			$this->getPluginName(), $a_img);
	}

	/**
	 * Get css file location
	 */
	public function getStyleSheetLocation($a_css_file)
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
	public function addBlockFile($a_tpl, $a_var, $a_block, $a_tplname)
	{
		$a_tpl->addBlockFile($a_var, $a_block,
			$this->getDirectory()."/templates/".$a_tplname);
	}


	/**
	 * @param $a_ctype
	 * @param $a_cname
	 * @param $a_slot_id
	 * @param $a_pname
	 *
	 * @description Create plugin record
	 */
	static public function createPluginRecord($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;

		ilCachedComponentData::flush();

		$q = "INSERT INTO il_plugin (component_type, component_name, slot_id, name)".
			" VALUES (".$ilDB->quote($a_ctype, "text").",".
			$ilDB->quote($a_cname, "text").",".
			$ilDB->quote($a_slot_id, "text").",".
			$ilDB->quote($a_pname, "text").")";

		$ilDB->manipulate($q);
	}


	/**
	 * Get record from il_plugin table
	 */
	static public function getPluginRecord($a_ctype, $a_cname, $a_slot_id, $a_pname)
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
	}

	/**
	 * Default initialization
	 */
	private function __init()
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
	 * (and should be made protected)
	 */
	abstract protected function slotInit();

	/**
	 * Object initialization. Can be overwritten by plugin class
	 * (and should be made protected)
	 */
	protected function init()
	{
	}

	/**
	 * Check whether plugin is active
	 */
	public function isActive()
	{
		global $ilPluginAdmin;

		return $ilPluginAdmin->isActive($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId(), $this->getPluginName());
	}

	/**
	 * Check whether update is needed.
	 */
	public function needsUpdate()
	{
		global $ilPluginAdmin;

		return $ilPluginAdmin->needsUpdate($this->getComponentType(),
			$this->getComponentName(), $this->getSlotId(), $this->getPluginName());
	}

	/**
	 * Install
	 *
	 * @return void
	 */
	public function install() {
		global $ilDB;

		ilCachedComponentData::flush();
		$q = "UPDATE il_plugin SET plugin_id = ".$ilDB->quote($this->getId(), "text").
					" WHERE component_type = ".$ilDB->quote($this->getComponentType(), "text").
					" AND component_name = ".$ilDB->quote($this->getComponentName(), "text").
					" AND slot_id = ".$ilDB->quote($this->getSlotId(), "text").
					" AND name = ".$ilDB->quote($this->getPluginName(), "text");

		$ilDB->manipulate($q);
		$this->afterInstall();
	}

	/**
	 * Activate
	 */
	function activate()
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
				$q = "UPDATE il_plugin SET active = ".$ilDB->quote(1, "integer").
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
	 * After install processing
	 *
	 * @return void
	 */
	protected function afterInstall()
	{
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
	function deactivate()
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
		$this->afterDeactivation();

		return $result;
	}


	/**
	 * After deactivation processing
	 */
	protected function afterDeactivation()
	{
	}
	
	
	protected function beforeUninstall()
	{
		// plugin-specific
		// false would indicate that anything went wrong
		return true; 
	}
	
	final function uninstall()
	{
		global $ilDB;
	
		if($this->beforeUninstall())
		{
			// remove all language entries (see ilObjLanguage)
			// see updateLanguages
			$prefix = $this->getPrefix();
			if($prefix)
			{
				$ilDB->manipulate("DELETE FROM lng_data".
					" WHERE module = ".$ilDB->quote($prefix, "text"));		
				$ilDB->manipulate("DELETE FROM lng_modules".
					" WHERE module = ".$ilDB->quote($prefix, "text"));
			}

			$this->clearEventListening();

			// db version is kept in il_plugin - will be deleted, too						
			
			$q = "DELETE FROM il_plugin".
				" WHERE component_type = ".$ilDB->quote($this->getComponentType(), "text").
				" AND component_name = ".$ilDB->quote($this->getComponentName(), "text").
				" AND slot_id = ".$ilDB->quote($this->getSlotId(), "text").
				" AND name = ".$ilDB->quote($this->getPluginName(), "text");
			$ilDB->manipulate($q);

			$this->afterUninstall();
			
			ilCachedComponentData::flush();
			return true;
		}		

		return false;
	}
	
	protected function afterUninstall()
	{
		// plugin-specific
	}
			
	/**
	 * Update plugin
	 */
	function update()
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

		$this->readEventListening();

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
	 * Read the event listening definitions from the plugin.xml (if file exists)
	 */
	protected function readEventListening()
	{
		$reader = new ilPluginReader($this->getDirectory() . '/plugin.xml',
			$this->getComponentType(), $this->getComponentName(), $this->getSlotId(), $this->getPluginName());
		$reader->clearEvents();
		$reader->startParsing();
	}


	/**
	 * Clear the entries of this plugin in the event handling table
	 */
	protected function clearEventListening()
	{
		$reader = new ilPluginReader($this->getDirectory() . '/plugin.xml',
			$this->getComponentType(), $this->getComponentName(), $this->getSlotId(), $this->getPluginName());
		$reader->clearEvents();
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
	static function getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		$slot_name = ilPluginSlot::lookupSlotName($a_ctype, $a_cname, $a_slot_id);

		$cached_component = ilCachedComponentData::getInstance();
		$rec = $cached_component->lookCompId($a_ctype, $a_cname);
		if (! $rec) {
			return NULL;
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
	 *
	 * @param string 	$a_ctype
	 * @param string 	$a_cname
	 * @param string 	$a_slot_id
	 * @param string 	$a_pname
	 *
	 * @return string[]
	 */
	static function lookupStoredData($a_ctype, $a_cname, $a_slot_id, $a_pname)
	{
		global $ilDB;

		$q = "SELECT * FROM il_plugin WHERE".
			" component_type = ".$ilDB->quote($a_ctype, "text")." AND".
			" component_name = ".$ilDB->quote($a_cname, "text")." AND".
			" slot_id = ".$ilDB->quote($a_slot_id, "text")." AND".
			" name = ".$ilDB->quote($a_pname, "text");

		$set = $ilDB->query($q);

		if($ilDB->numRows($set) == 0) {
			return array();
		}

		return $ilDB->fetchAssoc($set);
	}

	/**
	 * Get all active plugin names for a slot
	 */
	static function getActivePluginsForSlot($a_ctype, $a_cname, $a_slot_id)
	{
		global $ilPluginAdmin;

		$plugins = array();

		$cached_component = ilCachedComponentData::getInstance();

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
	 * Get All active plugin ids for a slot.
	 * @param $a_ctype
	 * @param $a_cname
	 * @param $a_slot_id
	 * @return array
	 */
	public static function getActivePluginIdsForSlot($a_ctype, $a_cname, $a_slot_id) {
		global $ilPluginAdmin;

		$plugins = array();
		$cached_component = ilCachedComponentData::getInstance();
		$lookupActivePluginsBySlotId = $cached_component->lookupActivePluginsBySlotId($a_slot_id);
		foreach($lookupActivePluginsBySlotId as $rec)
		{
			if ($ilPluginAdmin->isActive($a_ctype, $a_cname, $a_slot_id, $rec["name"]))
			{
				$plugins[] = $rec["plugin_id"];
			}
		}

		return $plugins;
	}

	/**
	 * Lookup name for id
	 */
	static function lookupNameForId($a_ctype, $a_cname, $a_slot_id, $a_plugin_id)
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
	static function lookupIdForName($a_ctype, $a_cname, $a_slot_id, $a_plugin_name)
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
